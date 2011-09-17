/**
 * @package		Gantry Template Framework - RocketTheme
 * @version		2.5.1 March 9, 2010
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license		http://www.rockettheme.com/legal/license.php RocketTheme Proprietary Use License
 */ 

var GantryBuildSpans=function(g,j,k){(g.length).times(function(i){var e="."+g[i];var f=function(a){a.setStyle('visibility','visible');var b=a.getText();var c=b.split(" ");first=c[0];rest=c.slice(1).join(" ");html=a.innerHTML;if(rest.length>0){var d=a.clone().setText(' '+rest),span=new Element('span').setText(first);span.inject(d,'top');a.replaceWith(d)}};$$(e).each(function(c){j.each(function(h){c.getElements(h).each(function(b){var a=b.getFirst();if(a&&a.getTag()=='a')f(a);else f(b)})})})})};