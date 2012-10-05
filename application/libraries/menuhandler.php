<?php

use Laravel\HTML, App\Models\System\Menu, App\Models\Account\Account;

class MenuHandler {

	public $groups = array();

	public $items;

	public static $cache_key = 'menu_';

	/**
	 * Конструктор
	 */
	public function __construct($groups, $attributes = array(), $element = 'ul')
	{
		$groups = !is_array($groups) ? array($groups) : $groups;

		// ситуация когда группы неизвестны. Например не создали гостевую группу.
		if (empty($groups))
			$groups = array(0);

		if (count($groups) > 1)
			array_multisort($groups);

		$this->groups = $groups;
		$this->items  = new MenuItems($attributes, $element);
	}

	/**
	 * Создаем MenuHandler инстанс
	 */
	public static function menu($groups, $attributes = array(), $element = 'ul')
	{
		return new static($groups, $attributes = array(), $element = 'ul');
	}

	/**
	 * Формируем иерархичную структуру из плоского массива
	 * Функция учитывает текущий аккаунт для построения меню
	 *
	 * @return MenuItems
	 */
	public function create_tree($flat_tree, $menuitems)
	{
		$depth = $flat_tree[0]->depth;

		foreach($flat_tree as $index => $node)
		{
			if ($depth != $node->depth)
				continue;

			if (!$node->active)
				continue;

			if (!Account::can($node->route))
				continue;

			$menuitems->add(URL::to_action($node->route), $node->title);

			if($node->right - $node->left > 1)
			{
				$size = ($node->right - $node->left - 1) / 2;

				$sub_tree = array_slice($flat_tree, $index + 1, $size);

				$menuitems->add_children($this->create_tree($sub_tree, new MenuItems()));

			}
		}

		return $menuitems;
	}

	/**
	 * Формируем иерархичную структуру меню по данным из бд
	 *
	 * @return MenuHandler
	 */
	public function items()
	{
		$group_key = self::$cache_key . implode('_', $this->groups);
		$header_key = self::$cache_key .'header';

		// Получаем кеш хедер в котором будут все закешированные меню
		$cache_header = Cache::get($header_key, array($group_key => 0));

		// Если комбинация есть в кеш хедере значит меню актуально
		if(!empty($cache_header[$group_key]))
			$cache_data = Cache::get($group_key);

		if(empty($cache_data))
		{
			$flat_tree = Menu::cget_flat_tree();

			// удаляем рутовый элемент
			array_shift($flat_tree);

			$cache_data = $this->create_tree($flat_tree, new MenuItems());

			// Добавляем комбинацию групп в кеш хедер
			$cache_header[$group_key] = 1;

			Cache::put($group_key, $cache_data, 0);
			Cache::put($header_key, $cache_header, 0);
		}

		$this->items = $cache_data;

		return $this;
	}

	/**
	 * Render menuitems to html
	 *
	 * @return string
	 */
	public function render($attributes = array(), $element = null)
	{
		$html = $this->render_items($this->items, $attributes, $element);

		return $html;
	}


	/**
	 * Get the evaluated string content of the view.
	 * 
	 * @param  MenuItems 	$menuitems         	The menu items to render
	 * @param  array  		$attributes 		Attributes for the element
	 * @param  string  		$element 			The type of the element (ul or ol)
	 * @return string
	 */
	public function render_items($menuitems, $attributes = array(), $element = null)
	{
		if ( ! isset($menuitems->items) || is_null($menuitems->items)) return '';

		$items = array();
		foreach ($menuitems->items as $item)
		{
			if ( ! array_key_exists('html', $item))
			{
				if($this->is_active($item))
					$item['list_attributes']['class'] = !empty($item['list_attributes']['class']) ? $item['list_attributes']['class'] .' active' : 'active';

				if($this->has_active_children($item))
					$item['list_attributes'] = !empty($item['list_attributes']['class']) ? $item['list_attributes']['class'] .' active-children' : 'active-children';
			}
			
			if (isset($item['children']->items))
			{
				$item['children'] = $this->render_items($item['children'], array('class' => 'dropdown-menu'), null);
				$item['list_attributes'] = array('class' => 'dropdown');
				$item['html'] = '<a class="dropdown-toggle" data-toggle="dropdown" href="#">'. $item['title'] .'<b class="caret"></b></a>';
			}
			else
				$item['children'] = '';

			$items[] = $this->render_item($item);
		}

		if (empty($attributes))
			$attributes = $menuitems->attributes;

		if (is_null($element))
			$element = $menuitems->element;
		
		return MenuHTML::$element($items, $attributes);
	}

	public function is_active($menuitem)
	{
		if ($menuitem['url'] == URL::current())
			return true;

		return false;
	}

	public function has_active_children($menuitem)
	{
		if ( ! isset($menuitem['children']->items))
			return false;

		foreach ($menuitem['children']->items as $child)
		{
			if ($this->is_active($child))
				return true;

			if (isset($child['children']->items))
				return $this->has_active_children($child);
		}
	}

	/**
	 * Turn item data into HTML
	 * 
	 * @param 	array 	$item 		The menu item
	 * @return 	string 	The HTML
	 */
	protected function render_item($item)
	{
		extract($item);

		if (array_key_exists('html', $item))
			return MenuHTML::$list_element($html.PHP_EOL.$children, $list_attributes);
		else
			return MenuHTML::$list_element(MenuHTML::link($url, $title, $link_attributes).PHP_EOL.$children, $list_attributes);
	}
}

class MenuItems {

	/**
	 * The menu items
	 * 
	 * @var array
	 */
	public $items = array();

	/**
	 * The menu's attributes
	 */
	public $attributes = array();
	
	/**
	 * The menu's element
	 */
	public $element;

	/**
	 * Create a new MenuItems instance
	 */
	public function __construct($attributes = array(), $element = 'ul')
	{
		$this->attributes = $attributes;
		$this->element = $element;
	}

	
	/**
	 * Add a menu item to the MenuItems instance.
	 *
	 * <code>
	 *		// Add a item to the default main menu
	 *		Menu::add('home', 'Homepage');
	 *
	 *		// Add a subitem to the homepage
	 *		Menu::add('home', 'Homepage', Menu::items()->add('home/sub', 'Subitem'));
	 *
	 *		// Add a item that has attributes applied to its tag
	 *		Menu::add('home', 'Homepage', null, array('class' => 'fancy'));
	 * </code>
	 *
	 * @param  string  $url
	 * @param  string  $title
	 * @param  array   $attributes
	 * @param  array   $children
	 * @return MenuItems
	 */
	public function add($url, $title, $children = null, $link_attributes = array(), $list_attributes = array(), $list_element = 'li')
	{
		$this->items[] = compact('url', 'title', 'children', 'link_attributes', 'list_attributes', 'list_element');

		return $this;
	}

	public function add_children($children)
	{
		$this->items[count($this->items)-1]['children'] = $children;

		return $this;
	}

	/**
	 * Add a raw html item to the MenuItems instance.
	 *
	 * <code>
	 *		// Add a raw item to the default main menu
	 *		Menu::raw('<img src="img/seperator.gif">');
	 * </code>
	 *
	 * @param  string  $url
	 * @param  string  $title
	 * @param  array   $attributes
	 * @param  array   $children
	 * @return MenuItems
	 */
	public function raw($html, $children = null, $list_attributes = array(), $list_element = 'li')
	{
		$this->items[] = compact('html', 'children', 'list_attributes', 'list_element');
		
		return $this;
	}

}


class MenuHTML extends HTML {

	/**
	 * Generate an ordered or un-ordered list.
	 *
	 * @param  string  $type
	 * @param  array   $list
	 * @param  array   $attributes
	 * @return string
	 */
	public static function listing($type, $list, $attributes = array())
	{
		$html = '';

		if (count($list) == 0) return $html;

		foreach ($list as $key => $value)
		{
			// If the value is an array, we will recurse the function so that we can
			// produce a nested list within the list being built. Of course, nested
			// lists may exist within nested lists, etc.
			if (is_array($value))
				$html .= static::listing($type, $value);
			else
				$html .= $value;
		}

		return '<'.$type.static::attributes($attributes).'>'.$html.'</'.$type.'>';
	}

	/**
	 * Create a LI item
	 *
	 * @param  string   $value
	 * @param  array   	$attributes
	 * @return string
	 */
	public static function li($value, $attributes)
	{
		return '<li'.static::attributes($attributes).'>'.$value.'</li>';
	}

	/**
	 * Create a DT item
	 *
	 * @param  string   $value
	 * @param  array   	$attributes
	 * @return string
	 */
	public static function dt($value, $attributes)
	{
		return '<dt'.static::attributes($attributes).'>'.$value.'</dt>';
	}

	/**
	 * Create a set of DD breasts
	 *
	 * @param  string   $value
	 * @param  array   	$attributes
	 * @return string
	 */
	public static function dd($value, $attributes)
	{
		return '<dd'.static::attributes($attributes).'>'.$value.'</dd>';
	}

}