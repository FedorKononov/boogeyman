(function (j, n, h) {
    var q = 1,
        k = "//kaiban/",
        s = k + "js/",
        b;

    j.kaiban_ad_is_displayed = false;

    b = s + "context_static_v" + q + ".js";

    if (j.kaiban_context_callbacks){
        var t = n.createElement("script"),
            l = n.getElementsByTagName("script")[0];
        t.type = "text/javascript";
        t.src = b;
        l.parentNode.insertBefore(t, l)
    }
})(this, this.document);