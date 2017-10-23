jQuery(function($) {
    jQuery.redirect = function(url, data, method, target) {
        data = typeof data === "undefined" ? {} : data;
        method = typeof method === "undefined" ? "" : method;
        target = typeof target === "undefined" ? false : target;
        if (!(method in ["GET", "POST"])) {
            method = $.isEmptyObject(data) ? "GET" : "POST";
        }
        data = decodeURIComponent($.param(data));
        if (method.toUpperCase() === "GET" && url.indexOf("?") !== -1) {
            var parts = url.split("?");
            data = decodeURIComponent(parts[1]) + "&" + data;
            url = parts[0];
        }
        var form = $("<form>").attr({method: method, action: url});
        if (target) {
            form.attr("target", target);
        }
        $.each(data.split("&"), function(key, value) {
            var parts = value.split("=");
            var input = $("<input>").attr({type: "hidden", name: parts[0], value: parts[1]});
            form.append(input);
        });
        $("body").append(form);
        form.submit();
    };
    
    jQuery.fn.extend({
        appendByPrototype: function(content, name, index, func) {
            func = typeof func === "undefined" ? null : func;
            var element = $(this);
            var obj = $(content);
            if (func !== null) {
                func(obj);
            }
            var str = $(document.createElement("div")).append(obj.clone()).remove().html();
            var row = str.replace(new RegExp(name, "g"), index);
            element.append(row);
        }
    });
    
    $("[data-grid]").each(function() {
        var el = $(this);
        el.load(el.attr("data-grid"), {id: el.attr("id")});
    });
    
    $(document).on("click", "[data-confirm]", function() {
        return window.confirm($(this).attr("data-confirm"));
    });
    
    $(document).on("focus", "[data-pick]", function() {
        var el = $(this);
        var dateformat = "YYYY-MM-DD";
        var timeformat = "HH:mm:ss";
        var format;
        switch (el.attr("data-pick")) {
            case "datetime":
                format = dateformat + " " + timeformat; break;
            case "date":
                format = dateformat; break;
            case "time":
                format = timeformat; break;
            default:
                format = "";
        }
        if (format !== "") {
            el.parent().css("position", "relative");
            el.datetimepicker({
                format: format,
                ignoreReadonly: true,
                showTodayButton: true,
                showClear: true
            });
        }
    });
});
