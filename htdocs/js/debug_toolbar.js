<?php namespace Twlan;
if (!framework\Provider::$debugEnabled)
{
    return;
}
if (isset($_GET["phpinfo"])) {
    header('Content-Type: text/html; charset=utf-8');
    phpinfo();
    exit;
}
?>
var syntaxHighlighter = {
    prepare: function(brush, text, target)
    {
        var dataTarget = $('<div class="syntaxhighlighter-wrap">');
        target.append(dataTarget.append(
            '<script type="syntaxhighlighter" class="brush: '+brush+'"><![CDATA[ '+ text +' ]]></script>'));
        SyntaxHighlighter.highlight(dataTarget);
        return dataTarget;
    }
};
$(document).ready(function() {
    if (window.initDebug) return;
    window.initDebug=true;
    /* DEPENDENCIES */
    $("body").append('<link rel="stylesheet" href="debug_toolbar.css" type="text/css" media="screen" />');
    $('body').append('<script type="text/javascript" src="shCore.js"></script>')
        .append('<script type="text/javascript" src="shBrushSql.js"></script>')
        .append('<link rel="stylesheet" href="shCore.css" type="text/css" media="screen" />')
        .append('<link rel="stylesheet" href="shThemeRDark.css" type="text/css" media="screen" />');
});
var debugToolBar = {
    toolbar: null,
    panel: null,
    panels: {
        "$active": "",
        database: function (data)
        {
            var _this = this;
            var contentTarget = $("<div class=\"debugt-panel-body\">");
            _this.$active.append(contentTarget);

            if (this.$active == null) return alert("Error while displaying database panel!");
            contentTarget.append("<h1 class=\"debugt-panel-heading\">Database activity</h1>");

            var numQuery = 1;
            Object.keys(data).forEach(function (k) {
                var heading = $("<div class=\"debugt-panel-elem\">" + numQuery + " - " + data[k].time + "</div>");
                if (data[k]["cached"]) heading.css("background-color", "greenyellow");
                contentTarget.append(heading);
                var dataTarget = syntaxHighlighter.prepare("sql;gutter: false;", data[k].query, contentTarget);

                var numEntry = 1;
                var traceTable = $("<table>");
                Object.keys(data[k].trace).forEach(function (_k) {
                    var traceObj = data[k].trace[_k];
                    traceTable.append("<tr><td>#" + numEntry + "</td> <td>" + traceObj["class"] + traceObj["type"] +
                        traceObj["function"] + "</td><td> " + traceObj["file"] + "</td><td>@" + traceObj["line"] + "</td></tr>");
                    ++numEntry;
                });

                contentTarget.append(traceTable);
                ++numQuery;
                dataTarget.find('.sql .value').each(function (_k, elem) {
                    $(elem).append(data[k].params[_k]);
                });
            });
        },
        console: function (data) {
            var _this = this;
            var contentTarget = $("<div class=\"debugt-panel-body\">");
            _this.$active.append(contentTarget);

            if (this.$active == null) return alert("Error while displaying console panel!");
            contentTarget.append("<h1 class=\"debugt-panel-heading\">Console</h1>");

            var numLogEntry = 1;
            Object.keys(data).forEach(function (k) {
                contentTarget.append("<div class=\"debugt-panel-elem\">" + numLogEntry + "." + data[k].message + "</div>");
                var traceTable = $("<table>");
                var numEntry = 1;
                Object.keys(data[k].trace).forEach(function (_k) {
                    var traceObj = data[k].trace[_k];
                    traceTable.append("<tr><td>#" + numEntry + "</td> <td>" + traceObj["class"] + traceObj["type"] +
                        traceObj["function"] + "</td><td> " + traceObj["file"] + "</td><td>@" + traceObj["line"] + "</td></tr>");
                    ++numEntry;
                });
                contentTarget.append(traceTable);
                contentTarget.append(data[k].time);
                ++numLogEntry;
            });
        },
        php: function (data) {
            var _this = this;
            var contentTarget = $("<div class=\"debugt-panel-body\">");
            _this.$active.append(contentTarget);

            if (this.$active == null) return alert("Error while displaying php log panel!");
            contentTarget.append("<h1 class=\"debugt-panel-heading\">PHP Log</h1>");

            var numLogEntry = 1;
            Object.keys(data).forEach(function (k) {
                contentTarget.append("<div class=\"debugt-panel-elem\">" + numLogEntry + "." +
                    " #" + data[k].code + " " + data[k].message + " " + data[k].file + "@" + data[k].line + "</div>");
                var traceTable = $("<table>");
                var numEntry = 1;
                Object.keys(data[k].trace).forEach(function (_k) {
                    var traceObj = data[k].trace[_k];
                    traceTable.append("<tr><td>#" + numEntry + "</td> <td>" + traceObj["class"] + traceObj["type"] +
                        traceObj["function"] + "</td><td> " + traceObj["file"] + "</td><td>@" + traceObj["line"] + "</td></tr>");
                    ++numEntry;
                });
                contentTarget.append(traceTable);
                ++numLogEntry;
            });
        },
        phpInfo: function () {
            var _this = this;
            var contentTarget = $('<iframe class="debugt-panel-body" style="width: 100%; height: 100%" src="debug_toolbar.js?phpinfo"/>');
            _this.$active.append(contentTarget);
        },
        adminer: function () {
            var _this = this;
            var contentTarget = $('<iframe class="debugt-panel-body" style="width: 100%; height: 100%" src="/adminer.php"/>');
            _this.$active.append(contentTarget);
        }
    },
    toggle: function() {
        if (this.toolbar == null) return;
        this.toolbar.slideToggle();
        
        $(".debugt-panel").slideUp();
    },
    init: function (data) {
        var _this = this;
        var toggleBtn = $('<img src="graphic/twlan_logo.jpg" style="z-index: 20000"></img>');
        //var toggleLink = $('<a href class="debugt-toggle">Toggle debugtoolbar</a>');
        toggleBtn.click(function() { _this.toggle(); });
        //toggleLink.click(function(e) { e.preventDefault(); _this.toggle(); });
        //$("body").append(toggleLink);

        var wrapDiv = $('<div class="debugt-wrap"></div>');
        var toolbarDiv = $('<div id="debugt-toolbar"></div>');
        this.toolbar = toolbarDiv;
        this.panels.$active = $('<div class="debugt-panel"></div>');


        var toolbarNav = $('<div id="debugt-nav">');
        toolbarNav.append($('<a class="debugt-nav-entry debugt-logo" href=""></a>').append(toggleBtn));
        toolbarNav.append('<a class="debugt-nav-entry" href="">Version<div><small><?php echo Globals::TWLAN_VERSION."-r".Globals::TWLAN_REVISION." <br>based on TW ".Globals::DS_VERSION; ?></small></div></a>');
        var phpVersion = $('<a class="debugt-nav-entry" href="">PHP-Version<div><small><?php echo phpversion(); ?></small></div></a>');
        var adminer = $('<a class="debugt-nav-entry" href="">Adminer</div></a>');
        toolbarNav.append(phpVersion).append(adminer);

        function clickHandler(navElem, name, data)
        {
            navElem.click(function (evt) {
                evt.preventDefault();

                if (_this.activePanel == name)
                {
                    _this.panels.$active.slideUp(400);
                    navElem.removeClass("active");
                    _this.activePanel = null;
                    return;
                }
                else
                {
                    _this.panels.$active.slideDown(400, function() {
                        _this.panels.$active.css("display", "block").css("overflow", "visible");
                    });
                    $(".debugt-nav-entry").removeClass("active");
                    _this.activePanel = name;
                    navElem.addClass("active");
                }

                _this.panels.$active.empty();
                if(!_this.panels[name]) return alert("Unsupported " + name);
                _this.panels[name](data);
            });
        }
        clickHandler(phpVersion, "phpInfo", null);
        clickHandler(adminer, "adminer", null);

        Object.keys(data).forEach(function (k) {
            var navElem = $('<a class="debugt-nav-entry" href="">');
            navElem.append(document.createTextNode(k));
            var textElem = $('<small>'+data[k].length+' entries</small>');
            navElem.append($('<div></div>').append(textElem));
            if (k == "database") {
                var _cached = 0;
                Object.keys(data[k]).forEach(function (_k) {
                    if (data[k][_k]["cached"]) _cached++;
                });
                //if (_cached)
                textElem.append(" (" + _cached + " cached)");
            }
            toolbarNav.append(navElem);
            clickHandler(navElem, k, data[k]);
        });
        toolbarDiv.append(toolbarNav);
        wrapDiv.append(toolbarDiv);

        $("body").prepend(wrapDiv).append(this.panels.$active);

        $(".debugt-nav-entry").on("click", function (evt) {
            evt.preventDefault();
        });
        //this.toolbar.slideToggle();
        $(document).ready(function() {
            SyntaxHighlighter.all();
        });
    }
};

var debug = function()
{
    debugToolBar.toggle();
};
