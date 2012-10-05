<?php namespace App\Models;

use App\Models\EloquentFix, DB, HTML, Cache;

class BaseCatalog extends EloquentFix {

	public $children = array();

	/**
	 * Получаем потоков модели
	 *
	 * @param   int  $depth
	 * @param   bool  $ar_model
	 * @return  array
	 */
	public function descendants($depth = null, $ar_model = false)
	{
		// Если свойство точно false то потомков нет
		if ($this->children === false)
		{
			return array();
		}

		if (empty($this->children))
		{
			// Получаем потомков из базы
			$children_array = $this->query_descendants($depth);

			if (empty($children_array))
			{
				$this->children = false;
				return $this->children();
			}

			if ($this->fill_children($children_array, $ar_model) === false)
			{
				$this->children = false;
				return $this->children();
			}
		}

		return $this->children;
	}


	/**
	 * Получаем детей модели
	 *
	 * @param   bool  $ar_model
	 * @return  array
	 */
	public function children($ar_model = false)
	{
		return $this->descendants($this->depth + 1, $ar_model);
	}


	/**
	 * Запрос на получение всех потомков
	 *
	 * @param   int  $depth
	 * @return  array
	 */
	public function query_descendants($depth = null, $columns = array('*'))
	{

		$query = 'SELECT '. implode(',', $columns) .' FROM '. static::$table .' WHERE `left` > '. $this->left .' AND `left` < '. $this->right;

		if ($depth)
			$query .= ' AND `depth` >= '. $depth;

		$query .= ' ORDER BY `left` ASC';

		return DB::connection(static::$connection)->query($query);
	}


	/**
	 * Запрос на получение детей
	 *
	 * @return  array
	 */
	public function query_children()
	{
		return $this->query_descendants($this->depth + 1);
	}


	/**
	 * Заполняем свойства детей и формируем иерархичный массив из входящего плоского
	 *
	 * @param   array  $children
	 * @return  BaseCatalog
	 */
	protected function fill_children(array $children_array = array(), $ar_model = false)
	{
		$l     = 0;
		$stack = array();

		foreach ($children_array as $child)
		{
			// Создаем модель если нужно
			$node = $ar_model ? new static((array) $child) : $child;

			if($ar_model)
				$node->exists = true;

			$l = count($stack);

			// Проверяем если мы работаем с разными уровнями
			while ($l > 0 && $stack[$l - 1]->depth >= $node->depth)
			{
				array_pop($stack);
				$l--;
			}

			// Первый элемент
			if ($l == 0)
			{
				$i = count($this->children);
				$this->children[$i] = $node;
				$stack[] = &$this->children[$i];
			}
			else
			{
				// @ нужна чтобы не сработал обработчик ошибок на undefined property children  при уровне E_ALL
				@$i = count($stack[$l - 1]->children);
				$stack[$l - 1]->children[$i] = $node;
				$stack[] = &$stack[$l - 1]->children[$i];
			}
		}

		if (empty($this->children))
		{
			return false;
		}

		return $this;
	}


	/**
	 * Сделать текущую модель ребенком для модели $parent
	 *
	 * @param   BaseCatalog  $parent
	 * @throws  BaseCatalogException
	 * @return  BaseCatalog
	 */
	public function child_of(BaseCatalog &$parent, $position = 'last')
	{
		if (!$parent->exists)
		{
			throw new BaseCatalogException('The parent BaseCatalog model must exist before you can assign children to it.');
		}

		if (!in_array($position, array('first', 'last')))
		{
			throw new BaseCatalogException("Position [$position] is not a valid position");
		}

		// Сбрасываем закешированных детей
		$parent->children = array();

		// Если элемент новый и еще не сохранен
		if (!$this->exists)
		{
			// заполняем мета данные
			$this->left = ($position === 'first') ? $parent->left + 1 : $parent->right;
			$this->right = $this->left + 1;
			$this->depth = $parent->depth + 1;

			// стартуем тразакцию
			DB::connection(static::$connection)->pdo->beginTransaction();

			// пересчитываем вложенное дерево
			$this->nest($this->left);

			// обновляем данные о родителе
			$parent->reload_meta_cols();

			$this->save();

			// коммитим
			DB::connection(static::$connection)->pdo->commit();
		}

		// Если модель уже существует.
		else
		{
			// стартуем тразакцию
			DB::connection(static::$connection)->pdo->beginTransaction();

			// выносим узел и его множество из дерева
			$this->remove_from_tree();

			// обновляем данные о родителе
			$parent->reload_meta_cols();

			// определяем новую позицию
			$new_left = ($position === 'first') ? $parent->left + 1 : $parent->right;

			// вычитываем дельту глубины
			$depth_delta = $parent->depth - $this->depth;

			// перемещаем поддерево
			$this->reinsert_in_tree($new_left, $depth_delta);

			// все изменения позади... коммитим
			DB::connection(static::$connection)->pdo->commit();

			// сбрасываем детей, потому что эти данные могли изменится и их надо получать заного
			$this->children = array();

			// обновляем мета данные
			$this->reload_meta_cols();
		}

		return $this;
	}


	/**
	 * Пересчет мета данных для подмоножества
	 *
	 * @param   int  $start
	 * @param   int  $size
	 * @return  BaseCatalog
	 */
	protected function nest($start, $size = null)
	{
		if ($size === null)
		{
			$size = $this->size() + 1;
		}

		$this->query()
			 ->where('left', '>=', $start)
			 ->update(array(
				'left' => DB::raw('`left` + '. $size),
			));

		$this->query()
			 ->where('right', '>=', $start)
			 ->update(array(
				'right' => DB::raw('`right` + '.$size),
			));

		return $this;
	}


	/**
	 * Получаем размер вложенного множества
	 *
	 * @return  int
	 */
	public function size()
	{
		return $this->right - $this->left;
	}

	/**
	 * Проверяем наличие детей
	 *
	 * @return  bool
	 */
	public function has_children()
	{
		return $this->size() > 1;
	}

	/**
	 * Перзагружаем мета данные о узле дерева
	 *
	 * @throws  BaseCatalogException
	 * @return  BaseCatalog
	 */
	public function reload_meta_cols()
	{
		if (!$this->exists)
		{
			throw new BaseCatalogException('You cannot call reload() on a model that hasn\'t been persisted to the database.');
		}
		
		$attributes = $this->query()->where(static::key(), '=', $this->{static::key()})->first();

		$to_override = array('left' => null, 'right' => null, 'depth' => null);

		foreach ($to_override as $attribute => $value)
			$to_override[$attribute] = $attributes->{$attribute};

		$this->fill($to_override);

		return $this;
	}

	/**
	 * Используется чтобы вынести узел и её множество из дерева 
	 * Данные продолжают оставатся в базе.
	 * Используется для промежуточных перестановок.
	 *
	 * @return  BaseCatalog
	 */
	protected function remove_from_tree()
	{
		// Нам нужно вывести узел и его детей из дерева.
		// Для этого мы должны происвоить правому смещению 0
		$delta = 0 - $this->right;

		// Смещаем узел и его подмножество "за дерево" используя детльту
		$this->query()
			 ->where('left', 'BETWEEN', DB::raw($this->left .' AND '. $this->right))
			 ->update(array(
				'left'  => DB::raw('`left` + '. $delta),
				'right' => DB::raw('`right` + '. $delta),
			));
		
		// Убираем отступ который был создан. Знак - во втором аргументе важен.
		$this->nest($this->left, - ($this->size() + 1));

		return $this->reload_meta_cols();
	}

	/**
	 * Удаляем элемент и его подмножество из дерева на совсем
	 *
	 * @return bool
	 */
	public function real_delete_from_tree()
	{
		// стартуем тразакцию
		DB::connection(static::$connection)->pdo->beginTransaction();

		// выносим узел и его множество из дерева
		$this->remove_from_tree();

		// удаляем вынесеные элементы из базы
		$this->query()
			 ->where('left', 'BETWEEN', DB::raw((0 - $this->size()).' AND 0'))
			 ->delete();

		// удаляем модель
		$this->delete();

		// все изменения позади... коммитим
		DB::connection(static::$connection)->pdo->commit();

		return true;
	}

	/**
	 * Добавляем в дерево структуру заранее вынесеную из дерева.
	 *
	 * @param   int  $left
	 * @return  BaseCatalog
	 */
	protected function reinsert_in_tree($left, $depth_delta)
	{
		// Делаем остпут для вложенных элементов
		$this->nest($left);

		// Возвращаем в дерево вынесеную до этого часть
		$this->query()
			 ->where('left', 'BETWEEN', DB::raw((0 - $this->size()).' AND 0'))
			 ->update(array(
				'left'  => DB::raw('`left` + '. ($left + $this->size())),
				'right' => DB::raw('`right` + '. ($left + $this->size())),
				'depth' => DB::raw('`depth` + '. ($depth_delta + 1)),
			));

		return $this;
	}


	/**
	 * Получаем все дерево в виде плоского массива
	 *
	 * @return  array
	 */
	public static function get_flat_tree()
	{
		return DB::connection(static::$connection)->query('SELECT * FROM '. static::$table .' ORDER BY `left` ASC');
	}

	/**
	 * Получаем плоское дерево из кеша или кешируем если его нету
	 *
	 * @param int $ttl
	 * @return array
	 */
	public static function cget_flat_tree($ttl = 0)
	{
		$key = get_called_class(). '_flat_tree';

		$result = Cache::get($key);

		if ($result)
			return $result;

		$result = static::get_flat_tree();

		Cache::put($key, $result, $ttl);
		
		return $result;
	}

	/**
	 * Сбрасываем весь кеш для вызываемой модели
	 */
	public static function cflush_all()
	{
		$keys_prefix = array('_flat_tree');

		foreach ($keys_prefix as $prefix)
			Cache::forget(get_called_class() . $prefix);

		return true;
	}

	/**
	 * Проверяем является ли текущий объект ребенком (не потомком!) другого объекта
	 *
	 * @param $item BaseCatelog
	 * @return bool
	 */

	public function is_child_of($item){
		return $this->left > $item->left && $this->right < $item->right && ($this->depth - 1) == $item->depth;
	}

	/**
	 * Подгружаем потомков по заданным узлам. Использюется для дополнения выбраной области дерева.
	 * 
	 * @param $nodes array
	 * @return array
	 */
	public static function descendants_subload($nodes)
	{
		if(!is_array($nodes))
			return array();

		$loaded = array();

		foreach($nodes as $node)
		{
			if(in_array($node, $loaded))
				continue;

			$object = static::find($node);

			if($object)
			{
				$loaded[] = $object->id;

				// если нету потомков
				if (!$object->has_children())
					continue;

				$descendants = $object->query_descendants(null, array('id'));

				foreach ($descendants as $descendant)
					$loaded[] = $descendant->id;
			}

			unset($object);
		}

		return $loaded;
	}

	/**
	 * Извлекаем из списка нод элементы которые являются родителями первого уровня. (корни данного подмножества)
	 * 
	 * @param $nodes array
	 * @return array
	 */
	public static function root_parents_filter($nodes)
	{
		if(!is_array($nodes))
			return array();

		$placeholders = substr(str_repeat('?,', count($nodes)), 0, -1);

		$query = 'SELECT id, depth FROM '. static::$table .' WHERE id IN ('. $placeholders .') ORDER BY depth ASC';

		$items = DB::connection(static::$connection)->query($query, $nodes);

		$return_ar = array();

		foreach ($items as $key => $node)
		{
			if ($key == 0)
			{
				$prev_depth = $node->depth;

				$return_ar[] = $node->id;

				continue;
			}

			if($prev_depth < $node->depth)
				break;

			$return_ar[] = $node->id;
		}

		return $return_ar;
	}
}

class BaseCatalogException extends \Exception {}
