<?php
/*
 * Copyright (c) 2020-2025 Aimy Extensions, Netzum Sorglos Software GmbH
 *
 * https://www.aimy-extensions.com/
 *
 * License: GNU GPLv2, see LICENSE.txt within distribution and/or
 *          https://www.aimy-extensions.com/software-license.html
 */
 defined( '_JEXEC' ) or die(); use Joomla\CMS\Form\FormField; use Joomla\CMS\HTML\HTMLHelper; use Joomla\CMS\Language\Text; use Joomla\CMS\Factory; class JFormFieldProOnlyNotice extends FormField { protected $type = 'proonlynotice'; public function getInput() { HTMLHelper::_( 'jquery.framework' ); $doc = Factory::getDocument(); $doc->addScriptDeclaration( 'jQuery(document).ready(function($)' . '{' . '$(".control-group")' . '.find(' . '"fieldset.aimy-pro-feature, div.aimy-pro-feature, textarea.aimy-pro-feature"' . ')' . '.each(function()' . '{' . 'var $this = $(this);' . '$this.parent().find("input,textarea,select,label")' . '.attr("disabled", "disabled")' . '.unbind()' . '.click(function(){return false;});' . '$this.addClass("pro-only-opt")' . '.parent().append(' . '$( "<div></div>" )' . '.addClass("pro-only")' . '.html(' . '$( "<a></a>" ).attr({' . 'href: "https://www.aimy-extensions.com/joomla/canonical.html",' . 'target: "_blank",' . 'rel: "noopener"' . '})' . '.text("' . Text::_( 'AIMY_CNCL_PRO_FEATURE' ) . '")' . ')' . ');' . '});' . '});' ); $doc->addStyleDeclaration( '.pro-only' . '{' . 'font-size: 0.9em;' . 'font-style: italic;' . 'font-color: #333;' . 'padding: 2px 0;' . '}' . '.pro-only-opt *[disabled="disabled"]' . '{' . 'opacity: 0.5;' . '}' . 'fieldset .pro-only a[target="_blank"]::before' . '{' . 'padding-right: 4px;' . '}' ); return ''; } public function getLabel() { return ''; } } 
