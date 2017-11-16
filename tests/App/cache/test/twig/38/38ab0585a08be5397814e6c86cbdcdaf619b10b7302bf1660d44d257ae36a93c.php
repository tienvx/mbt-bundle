<?php

/* @EasyAdmin/css/easyadmin.css.twig */
class __TwigTemplate_cbfdbefc97b3169416d5c7cf9c652431432b09006a10a6e78c85e9791dfc68af extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 4
        echo "
";
        // line 5
        $context["color_schemes"] = array("dark" => array("danger" => "#D42124", "success" => "#006B2E", "text" => "#222222", "text_muted" => "#737373", "link" => "#205081", "black" => "#111111", "white" => "#FFFFFF", "gray_darker" => "#252525", "gray_dark" => "#444", "gray" => "#AAA", "gray_light" => "#CCC", "gray_lighter" => "#F5F5F5", "page_background" => "#F5F5F5", "table_header" => "#EEE", "table_border" => "#CCC", "table_row_border" => "#DDD"), "light" => array("danger" => "#D42124", "success" => "#006B2E", "text" => "#444444", "text_muted" => "#737373", "link" => "#205081", "black" => "#333333", "white" => "#FFFFFF", "gray_darker" => "#444", "gray_dark" => "#AAA", "gray" => "#CCC", "gray_light" => "#F5F5F5", "gray_lighter" => "#FAFAFA", "page_background" => "#FFFFFF", "table_header" => "#FAFAFA", "table_border" => "#FFFFFF", "table_row_border" => "#E5E5E5"));
        // line 43
        echo "
";
        // line 44
        $context["colors"] = twig_get_attribute($this->env, $this->getSourceContext(), ($context["color_schemes"] ?? null), ($context["color_scheme"] ?? null), array(), "array");
        // line 45
        echo "
";
        // line 52
        echo ".sf-toolbarreset {
    -webkit-font-smoothing: subpixel-antialiased;
    -moz-osx-font-smoothing: auto;
}

";
        // line 60
        echo "body {
    font-family: Helvetica, \"Helvetica Neue\", Arial, sans-serif;
}

";
        // line 66
        echo "a        { color: ";
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "link", array());
        echo "; }
a:hover  { opacity: 0.9; }
a:active { outline: 0; }

#main a:active {
    position: relative;
    top: 1px;
}

a.text-primary,
a.text-primary:focus {
    color: ";
        // line 77
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "link", array());
        echo ";
}
a.text-danger,
a.text-danger:focus {
    color: ";
        // line 81
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo ";
}
a.text-primary:hover,
a.text-danger:hover {
    opacity: 0.9;
}

";
        // line 90
        echo "ul, ol {
    margin: 1em 0 1em 1em;
    padding: 0;
}
li {
    margin-bottom: 1em;
}

ul.inline {
    list-style: none;
    margin: 0;
}
ul.inline li {
    margin: 0;
}

";
        // line 108
        echo "div.flash {
    padding: .5em;
}
div.flash-success {
    background: ";
        // line 112
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "success", array());
        echo ";
    color: ";
        // line 113
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
}
div.flash-error {
    background: ";
        // line 116
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo ";
    color: ";
        // line 117
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
}
div.flash-error strong {
    padding-right: .5em;
}

";
        // line 126
        echo ".label:not([class*=label-]) {
    background: ";
        // line 127
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_darker", array());
        echo ";
}
.label {
    color: ";
        // line 130
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
    display: inline-block;
    font-size: 11px;
    padding: 4px;
    text-transform: uppercase;
}

.label-success {
    ";
        // line 139
        echo "    background: ";
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "success", array());
        echo " !important;
}
.label-danger {
    ";
        // line 143
        echo "    background: ";
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo " !important;
}
.label-empty {
    background: transparent;
    border: 2px dotted;
    border-radius: 4px;
    color: ";
        // line 149
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
        echo ";
    padding: 4px 8px;
}

";
        // line 154
        echo ".boolean .label,
.toggle .label {
    min-width: 33px;
}

";
        // line 161
        echo ".toggle.btn-xs {
 width: 44px;
}
.toggle-group .btn,
.toggle-group .btn:hover {
   border-radius: 3px;
   font-size: 10px;
   font-weight: bold;
   text-transform: uppercase;
}
.toggle-group .btn {
   padding: 4px 6px;
}
.toggle-group .btn:hover {
    border: 0;
}
.toggle-group .btn + .btn {
    margin-left: 0;
}
.toggle-group .toggle-on,
.toggle-group .toggle-on.btn-xs,
.toggle-group .toggle-on:hover,
.toggle-group .toggle-on:active,
.toggle-group .toggle-on:active:hover {
    background: ";
        // line 185
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "success", array());
        echo ";
    border-color: ";
        // line 186
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "success", array());
        echo ";
    color: ";
        // line 187
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
    padding: 4px 5px 4px 0;
    border: 0;
}
.toggle-group .toggle-off,
.toggle-group .toggle-off.btn-xs,
.toggle-group .toggle-off:hover,
.toggle-group .toggle-off:active,
.toggle-group .toggle-off:active:hover {
    background: ";
        // line 196
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo ";
    border-color: ";
        // line 197
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo ";
    color: ";
        // line 198
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
    padding: 4px 0 4px 5px;
    border: 0;
}
.toggle-group .toggle-handle,
.toggle-group .toggle-handle:hover,
.toggle-group .toggle-handle:active,
.toggle-group .toggle-handle:active:hover {
    background: ";
        // line 206
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
        echo ";
    border: 0;
    border-radius: 2px;
    height: 19px;
    margin-top: 2px;
    padding: 5px;
}
.toggle .btn-success .toggle-handle {
    box-shadow: -2px 0 1px rgba(0, 0, 0, .2);
}
.toggle .btn-danger .toggle-handle {
    box-shadow: 2px 0 1px rgba(0, 0, 0, .2);
}

";
        // line 222
        echo "span.badge {
    background-color: ";
        // line 223
        echo ($context["brand_color"] ?? null);
        echo ";
}

";
        // line 228
        echo ".btn:focus {
    outline: none;
}
.btn + .btn {
    margin-left: 5px;
}

button.btn:active {
    position: relative;
    top: 1px;
}

.btn,
.btn:hover,
.btn:active,
.btn:focus,
.btn:active:hover  {
";
        // line 245
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 246
            echo "    background: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
            echo ";
";
        } elseif (("light" ==         // line 247
($context["color_scheme"] ?? null))) {
            // line 248
            echo "    background: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray", array());
            echo ";
";
        }
        // line 250
        echo "    border: 1px solid transparent;
    border-radius: 4px;
    box-shadow: none;
    color: ";
        // line 253
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text", array());
        echo ";
    display: inline-block;
    line-height: 1.42857143;
    opacity: 1;
    outline: none;
    padding: 6px 12px;
    text-align: center;
}
.btn-xs,
.btn-xs:hover,
.btn-xs:active,
.btn-xs:focus,
.btn-xs:active:hover {
    padding: 1px 5px;
}

.btn-primary,
.btn-primary:hover,
.btn-primary:active,
.btn-primary:focus,
.btn-primary:active:hover {
    background-color: ";
        // line 274
        echo ($context["brand_color"] ?? null);
        echo ";
    border-color: transparent;
    color: ";
        // line 276
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
}
.btn-info,
.btn-info:hover,
.btn-info:active,
.btn-info:focus,
.btn-info:active:hover {
    background-color: #39a0ed;
    border-color: transparent;
    color: ";
        // line 285
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
}
.btn-default,
.btn-default:hover,
.btn-default:active,
.btn-default:focus,
.btn-default:active:hover {
    border-color: transparent;
}

.btn-danger,
.btn-danger:hover,
.btn-danger:active,
.btn-danger:focus,
.btn-danger:active:hover {
    background-color: ";
        // line 300
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo ";
    border-color: transparent;
    color: ";
        // line 302
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
}

.btn-success,
.btn-success:hover,
.btn-success:active,
.btn-success:focus,
.btn-success:active:hover {
    background-color: ";
        // line 310
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "success", array());
        echo ";
    border-color: transparent;
    color: ";
        // line 312
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
}

";
        // line 316
        echo ".btn-secondary,
.btn-secondary:hover,
.btn-secondary:active,
.btn-secondary:focus,
.btn-secondary:active:hover {
    background: transparent;
    color: ";
        // line 322
        echo ($context["brand_color"] ?? null);
        echo ";
    text-decoration: underline;
}
.btn-secondary:hover {
    text-decoration: none;
}

.btn-primary,
.btn-danger,
.btn-success,
.btn-info {
    font-weight: bold;
}

.btn i {
    font-size: 16px;
    margin-right: 2px;
}

.btn-flat,
.btn-flat:hover,
.btn-flat:active,
.btn-flat:focus,
.btn-flat:active:hover {
    border-radius: 0;
}

";
        // line 351
        echo ".form-inline .form-control {
    margin-bottom: 5px;
}
.form-control,
.form-inline .form-control {
    border: 1px solid ";
        // line 356
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray", array());
        echo ";
    border-radius: 0;
";
        // line 358
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 359
            echo "    box-shadow: 0 0 3px rgba(0, 0, 0, .15);
";
        }
        // line 361
        echo "    color: ";
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text", array());
        echo ";
    -webkit-transition: none;
    transition: none;
}
.form-control:focus {
";
        // line 366
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 367
            echo "    border-color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_dark", array());
            echo ";
    box-shadow: 0 0 3px rgba(0, 0, 0, .15);
";
        } elseif (("light" ==         // line 369
($context["color_scheme"] ?? null))) {
            // line 370
            echo "    border-color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_darker", array());
            echo ";
";
        }
        // line 372
        echo "}

.has-error .error-block {
    color: ";
        // line 375
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo ";
    font-weight: bold;
    padding-top: 5px;
}
.has-error .error-block .label-danger {
    margin-right: 3px;
}
.has-error .error-block ul {
    margin: .5em 0 .5em 1.5em;
}
.has-error .error-block ul li {
    margin-bottom: .5em;
}

.help-block,
.has-error .help-block {
    color: ";
        // line 391
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text_muted", array());
        echo ";
    font-size: 13px;
}
.nullable-control {
   padding-top: 5px;
}

.form-actions.stuck {
    bottom: 0;
    position: fixed;
";
        // line 401
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 402
            echo "    background-color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
            echo ";
    box-shadow: 0 -1px 4px ";
            // line 403
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
            echo ";
";
        } elseif (("light" ==         // line 404
($context["color_scheme"] ?? null))) {
            // line 405
            echo "    background-color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
            echo ";
    box-shadow: 0 -1px 4px ";
            // line 406
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray", array());
            echo ";
";
        }
        // line 408
        echo "    height: 52px;
    padding-top: 10px;
";
        // line 410
        if (((array_key_exists("kernel_debug", $context)) ? (_twig_default_filter(($context["kernel_debug"] ?? null), false)) : (false))) {
            // line 411
            echo "    height: 85px;
    padding-bottom: 45px;
";
        }
        // line 414
        echo "    z-index: 9999;
}

";
        // line 419
        echo ".field-collection .collection-empty {
    margin: .5em 0;
}

";
        // line 425
        echo ".field-divider hr {
    margin-top: 15px;
    margin-bottom: 26px;
    border: 0;
    border-top: 1px solid;
";
        // line 430
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 431
            echo "    border-top-color: #DDD;
";
        } elseif (("light" ==         // line 432
($context["color_scheme"] ?? null))) {
            // line 433
            echo "    border-top-color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
            echo ";
";
        }
        // line 435
        echo "}
.field-group .field-divider hr {
";
        // line 437
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 438
            echo "    border-top-color: #DDD;
";
        } elseif (("light" ==         // line 439
($context["color_scheme"] ?? null))) {
            // line 440
            echo "    border-top-color: #EEE;
";
        }
        // line 442
        echo "}

.field-section {
    margin: 16px 0 15px;
}
.field-section h2 {
";
        // line 448
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 449
            echo "    border-bottom: 1px solid ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
            echo ";
    color: ";
            // line 450
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_dark", array());
            echo ";
";
        } elseif (("light" ==         // line 451
($context["color_scheme"] ?? null))) {
            // line 452
            echo "    border-bottom: 1px solid #EEE;
    color: ";
            // line 453
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_darker", array());
            echo ";
";
        }
        // line 455
        echo "    font-size: 16px;
    padding-bottom: 6px;
}
.field-section h2 i {
";
        // line 459
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 460
            echo "    color: #555;
";
        } elseif (("light" ==         // line 461
($context["color_scheme"] ?? null))) {
            // line 462
            echo "    color: #777;
";
        }
        // line 464
        echo "    margin-right: 2px;
}
.field-section p.help-block {
    margin: 8px 0 0;
}

.field-group .box {
";
        // line 471
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 472
            echo "    border: 1px solid #DDD;
";
        } elseif (("light" ==         // line 473
($context["color_scheme"] ?? null))) {
            // line 474
            echo "    border: 1px solid #EEE;
";
        }
        // line 476
        echo "    box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05);
}
.field-group .box-header i {
";
        // line 479
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 480
            echo "    color: #555;
";
        } elseif (("light" ==         // line 481
($context["color_scheme"] ?? null))) {
            // line 482
            echo "    color: #777;
";
        }
        // line 484
        echo "    margin-right: 2px;
}
.field-group .box-header.with-border {
";
        // line 487
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 488
            echo "    background: #F0F0F0;
    border-bottom-color: #DDD;
";
        } elseif (("light" ==         // line 490
($context["color_scheme"] ?? null))) {
            // line 491
            echo "    background: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
            echo ";
    border-bottom-color: #EEE;
    color: ";
            // line 493
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_darker", array());
            echo ";
";
        }
        // line 495
        echo "    padding: 11px 10px 9px;
}
";
        // line 497
        if (("light" == ($context["color_scheme"] ?? null))) {
            // line 498
            echo ".field-group .box-header .box-title {
    color: #777;
}
";
        }
        // line 502
        echo ".field-group .box-body {
    padding: 15px 15px 5px;
}
.field-group .box-body > .help-block {
    margin-top: 0;
}

";
        // line 512
        echo ".select2-container {
    width: 100% !important;
}
.select2-container--bootstrap .select2-selection {
    border: 1px solid ";
        // line 516
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray", array());
        echo ";
    border-radius: 0;
    box-shadow: 0 0 3px rgba(0, 0, 0, .15);
    color: ";
        // line 519
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text", array());
        echo ";
    -webkit-transition: none;
    transition: none;
}
.select2-container--bootstrap .select2-selection .select2-search--inline {
    margin: 0;
}
.select2-container--bootstrap .select2-selection--single .select2-selection__rendered {
    color: ";
        // line 527
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text", array());
        echo ";
    padding-top: 4px;
}
.select2-container--bootstrap .select2-results__option {
    margin-bottom: 0;
}
.select2-container--bootstrap .select2-results__option[aria-selected=true] {
    background-color: ";
        // line 534
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
        echo ";
    font-weight: bold;
}
.select2-container--bootstrap .select2-results__option--highlighted[aria-selected] {
    background-color: ";
        // line 538
        echo ($context["brand_color"] ?? null);
        echo ";
}
.select2-container--bootstrap .select2-selection--multiple .select2-selection__choice {
    color: ";
        // line 541
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text", array());
        echo ";
}
.select2-container--bootstrap .select2-selection--multiple .select2-selection__choice__remove {
    color: ";
        // line 544
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo ";
    font-weight: bold;
    position: relative;
    top: -1px;
}
.select2-container--bootstrap .select2-search--dropdown .select2-search__field {
    border: 1px solid ";
        // line 550
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_dark", array());
        echo ";
    border-radius: 0;
    margin: 5px 10px;
    padding: 6px;
    width: 96%;
}
.select2-search--inline .select2-search__field:focus {
    outline: 0;
    border: 0;
}

";
        // line 563
        echo ".easyadmin-vich-image img {
    border: 3px solid ";
        // line 564
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
    box-shadow: 0 0 3px ";
        // line 565
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray", array());
        echo ";
    max-height: 300px;
    max-width: 400px;
}
.easyadmin-vich-image input[type=\"file\"],
.easyadmin-vich-file input[type=\"file\"] {
    padding-top: 7px;
}
.easyadmin-vich-file a {
    display: inline-block;
    padding-top: 7px;
}
";
        // line 578
        echo ".easyadmin-vich-file .field-checkbox {
    margin-bottom: 0;
}
.easyadmin-vich-file .field-checkbox .col-sm-2,
.easyadmin-vich-image .field-checkbox .col-sm-2 {
    display: none;
}

";
        // line 588
        echo ".easyadmin-thumbnail img {
    border: 3px solid ";
        // line 589
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
    box-shadow: 0 0 3px ";
        // line 590
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray", array());
        echo ";
    max-height: 100px;
    max-width: 100%;
}
.easyadmin-thumbnail img:hover {
    cursor: zoom-in;
}
.featherlight .easyadmin-lightbox:hover {
    cursor: zoom-out;
}
.easyadmin-lightbox {
    display: none;
}
.featherlight .easyadmin-lightbox {
    display: block;
}
.easyadmin-lightbox img {
    max-width: 100%;
}

";
        // line 612
        echo ".modal-dialog .modal-content {
    border-radius: 0;
}
.modal-dialog .modal-content .modal-body h4 {
    font-size: 21px;
    margin: .5em 0;
}
.modal-dialog .modal-footer {
    background: ";
        // line 620
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
        echo ";
    border-top: 1px solid ";
        // line 621
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
        echo ";
    margin-top: 1.5em;
}

";
        // line 627
        echo ".newrow, .new-row {
    clear: both;
    display: block;
}

";
        // line 635
        echo "
";
        // line 638
        echo ".content-wrapper {
    background: ";
        // line 639
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "page_background", array());
        echo ";
}
.fixed .content-wrapper {
    padding-top: 50px;
}

";
        // line 647
        echo ".main-header {
    background: ";
        // line 648
        echo ($context["brand_color"] ?? null);
        echo ";
    position: relative;
}
.main-header .logo {
    color: ";
        // line 652
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
    font-family: Helvetica, \"Helvetica Neue\", Arial, sans-serif; ";
        // line 654
        echo "    font-size: 18px;
    font-weight: bold;
    height: 45px;
    line-height: 45px;
    padding: 0;
}
.main-header .logo-long .logo-lg {
    font-size: 16px;
}
.main-header .logo-lg {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.main-header #header-logo {
}
.main-header img {
    margin-top: -2px;
    max-height: 36px;
}
.main-header li {
    margin-bottom: 0;
}

.main-header > .navbar {
    background-color: ";
        // line 679
        echo ($context["brand_color"] ?? null);
        echo ";
    color: rgba(255, 255, 255, 0.9);
    margin-left: 0;
    min-height: 40px;
}

.main-header .navbar .sidebar-toggle {
    color: rgba(255, 255, 255, 0.8);
    display: inline-block;
    font-size: 16px;
    height: 35px;
    left: 0;
    line-height: 35px;
    padding: 0 15px;
    position: absolute;
    text-align: center;
    top: 45px;
}
.sidebar-mini.sidebar-collapse .sidebar-toggle {
    color: ";
        // line 698
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
}

.navbar-custom-menu,
.navbar-custom-menu ul.navbar-nav,
.navbar-custom-menu ul.navbar-nav .user-menu {
    float: none;
}
.navbar-custom-menu {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    font-weight: bold;
    height: 35px;
    line-height: 35px;
    padding: 0 15px;
    text-align: right;
    width: 100%;
}

.main-header .navbar .user-menu .btn {
    background: rgba(255, 255, 255, 0.05);
    border-color: transparent;
    color: #fff;
}
.main-header .navbar .user-menu .btn:active {
    top: 0;
}
.main-header .navbar .user-menu .btn.dropdown-toggle {
    padding: 6px 8px;
}
.main-header .navbar .user-menu .btn-group:hover .btn {
    background: rgba(255, 255, 255, 0.1);
}
.main-header .navbar .user-menu .btn-group {
    height: 35px;
}
.main-header .navbar .user-menu .btn-group .btn {
    border-radius: 0;
}
.main-header .navbar .user-menu .dropdown-menu {
    background: #fff;
    box-shadow: 1px 1px 3px #ccc;
    border-radius: 2px;
    position: absolute;
    left: auto;
    right: 0;
}
.main-header .navbar .user-menu .dropdown-menu i {
    margin: 0;
}
.main-header .navbar .user-menu .dropdown-menu a {
    color: #777;
}

";
        // line 755
        echo "#content #main {
    padding-bottom: 3em;
}
.content {
    padding-top: 10px;
}
.content-header {
    padding: 12px 15px 0 15px;
}
.content-header h1 {
    margin: 0;
    font-size: 24px;
}

";
        // line 771
        echo ".main-sidebar,
.wrapper {
";
        // line 773
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 774
            echo "    background-color: #333;
";
        } elseif (("light" ==         // line 775
($context["color_scheme"] ?? null))) {
            // line 776
            echo "    background-color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
            echo ";
";
        }
        // line 778
        echo "}
.main-sidebar {
    padding-top: 80px;
}

.sidebar-menu li.header {
";
        // line 784
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 785
            echo "    color: #777;
";
        } elseif (("light" ==         // line 786
($context["color_scheme"] ?? null))) {
            // line 787
            echo "    color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_dark", array());
            echo ";
";
        }
        // line 789
        echo "    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}
.treeview-menu > li.header {
";
        // line 794
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 795
            echo "    background: #404040;
";
        } elseif (("light" ==         // line 796
($context["color_scheme"] ?? null))) {
            // line 797
            echo "    background-color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
            echo ";
";
        }
        // line 799
        echo "    padding-left: 28px;
}

.sidebar-menu li a,
.sidebar-menu li a span,
.sidebar-menu li.header,
.sidebar-mini.sidebar-collapse .sidebar-menu .treeview-menu a {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.sidebar-mini.sidebar-collapse .sidebar-menu li a {
    overflow: visible;
}

.sidebar-menu > li > a,
.sidebar-menu .treeview-menu > li > a {
";
        // line 816
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 817
            echo "    background: #333;
    color: #CCC;
";
        } elseif (("light" ==         // line 819
($context["color_scheme"] ?? null))) {
            // line 820
            echo "    color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_darker", array());
            echo ";
";
        }
        // line 822
        echo "    border-left: 3px solid transparent;
    display: block;
    font-weight: bold;
    opacity: 1;
}
.sidebar-menu .treeview-menu > li > a {
";
        // line 828
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 829
            echo "    background: #404040;
";
        } elseif (("light" ==         // line 830
($context["color_scheme"] ?? null))) {
            // line 831
            echo "    background-color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
            echo ";
";
        }
        // line 833
        echo "    font-size: 13px;
    padding: 8px 5px 8px 25px;
}
.sidebar-menu > li:hover > a,
.sidebar-menu .treeview-menu > li:hover > a,
.sidebar-menu > li.active > a,
.sidebar-menu .treeview-menu > li.active > a,
.sidebar-collapse .sidebar-menu > li.active.submenu-active > a,
.sidebar-collapse .sidebar-menu > li:hover .treeview-menu > li.active > a {
";
        // line 842
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 843
            echo "    color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
            echo ";
    background: #4D4D4D;
    border-left-color: #BBB;
";
        } elseif (("light" ==         // line 846
($context["color_scheme"] ?? null))) {
            // line 847
            echo "    background: #DCDCDC;
    border-left-color: #808080;
";
        }
        // line 850
        echo "}
.sidebar-menu > li > a > .fa {
    width: 22px;
}
.sidebar-menu .treeview-menu {
    padding: 0;
}

";
        // line 859
        echo ".sidebar-menu li > a > .pull-right {
    font-weight: bold;
    text-align: right;
}
";
        // line 864
        echo ".sidebar-menu li.active > a > .fa-angle-left {
    top: 30px;
    right: 0;
}

";
        // line 870
        echo ".sidebar-collapse .sidebar-menu > li > a {
    padding: 12px 5px 12px 12px;
}
.sidebar-collapse .sidebar-menu > li .treeview-menu > li > a {
    padding-left: 12px;
}
.sidebar-collapse .sidebar-menu > li > a > i.fa {
    font-size: 18px;
}
.sidebar-mini.sidebar-collapse .sidebar-menu > li > .treeview-menu {
    padding: 0;
}
.sidebar-collapse .sidebar-menu > li:hover > a,
.sidebar-collapse .sidebar-menu .treeview-menu > li:hover > a,
.sidebar-menu > li.active.submenu-active > a,
.sidebar-collapse .sidebar-menu > li.active.submenu-active:hover > a {
    border-left-color: transparent;
";
        // line 887
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 888
            echo "    background: #333;
";
        } elseif (("light" ==         // line 889
($context["color_scheme"] ?? null))) {
            // line 890
            echo "    background: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
            echo ";
";
        }
        // line 892
        echo "}

";
        // line 895
        echo ".sidebar-mini.sidebar-collapse .sidebar-menu li.header {
";
        // line 896
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 897
            echo "    border-bottom: 1px solid #777;
";
        } elseif (("light" ==         // line 898
($context["color_scheme"] ?? null))) {
            // line 899
            echo "    border-bottom: 1px solid #BBB;
";
        }
        // line 901
        echo "    display: block !important;
    font-size: 0;
    height: 1px;
    margin: 0;
    padding: 0;
}

";
        // line 911
        echo "body.easyadmin h1.title {
    margin-bottom: 10px;
}

.help-entity {
";
        // line 916
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 917
            echo "    color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_dark", array());
            echo ";
";
        } elseif (("light" ==         // line 918
($context["color_scheme"] ?? null))) {
            // line 919
            echo "    border: 1px solid #EEE;
    box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05);
    color: ";
            // line 921
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text_muted", array());
            echo ";
";
        }
        // line 923
        echo "    margin: 10px 0 5px;
}

";
        // line 929
        echo "
body.list .global-actions {
    display: table;
    width: 100%;
}
body.list .global-actions .button-action {
    display: table-cell;
    padding-left: 10px;
    vertical-align: middle;
    width: 120px;
}
body.list .global-actions .button-action a {
    float: right;
}
body.list .global-actions .form-action {
    display: table-cell;
    width: 100%;
}
body.list .global-actions .form-action .input-group {
    width: 100%;
}

body.list .global-actions .form-control {
    box-shadow: none;
}
body.list .global-actions .input-group-btn > button.btn:not(:last-child) {
    border-bottom-right-radius: 3px;
    border-top-right-radius: 3px;
}
body.list .global-actions .input-group-btn a.btn {
    border-radius: 3px;
    margin-left: 10px;
}

";
        // line 965
        echo "body.list .table-responsive,
body.list table {
    background: transparent;
    border: 0;
}
body.list table thead {
    display: none;
}
body.list .table tbody {
    background: transparent;
    border: 0;
}
body.list table tbody tr {
    background: ";
        // line 978
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
";
        // line 979
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 980
            echo "    border: 1px solid ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
            echo ";
";
        } elseif (("light" ==         // line 981
($context["color_scheme"] ?? null))) {
            // line 982
            echo "    border: 1px solid ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray", array());
            echo ";
";
        }
        // line 984
        echo "    display: block;
    margin-bottom: 1em;
}
body.list table tbody td {
    border-bottom: 1px solid ";
        // line 988
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "table_row_border", array());
        echo ";
    border-top: 0;
    display: block;
    vertical-align: middle;
}
body.list table tbody td:last-child {
    border-bottom: 0;
}
table td:before {
    content: attr(data-label);
    float: left;
    font-weight: bold;
}
table td:after {
    clear: both;
    content: \"\";
    display: block;
}
/* needed because the responsive tables require contents aligned to the right */
body.list table td,
body.list table .text-center,
body.list table .text-left,
body.list table .text-right {
    text-align: right;
}

body.list table tbody td.image .easyadmin-thumbnail img {
    border-width: 2px;
    max-height: 50px;
    max-width: 150px;
}
body.list table tbody td.association .badge {
    font-size: 13px;
}
body.list table tbody td.actions a {
    font-weight: bold;
    margin-left: 10px;
}

";
        // line 1029
        echo "body.list .table tbody span.highlight {
    background: #FF9;
    border-radius: 2px;
    padding: 1px;
}
body.list .table tbody .no-results span.highlight,
body.list .table tbody .actions span.highlight {
    background: 0;
    border-radius: 0;
}

";
        // line 1042
        echo "body.list .pagination {
    float: right;
    margin: 0;
}
body.list .pagination > .disabled > span {
    background: transparent;
    border: 1px solid transparent;
";
        // line 1049
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 1050
            echo "    color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text_muted", array());
            echo ";
";
        } elseif (("light" ==         // line 1051
($context["color_scheme"] ?? null))) {
            // line 1052
            echo "    color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray", array());
            echo ";
";
        }
        // line 1054
        echo "}
body.list .pagination > li > a {
    background: ";
        // line 1056
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
    border-color: ";
        // line 1057
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
        echo ";
    border-radius: 0;
    color: ";
        // line 1059
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text", array());
        echo ";
}
body.list .pagination > li > a:hover {
    background: ";
        // line 1062
        echo ($context["brand_color"] ?? null);
        echo ";
    color: ";
        // line 1063
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
}
.pagination > li > a,
.pagination > li > span {
    padding: 6px 8px;
}
body.list .pagination > li i {
    padding: 0 3px;
}
";
        // line 1075
        echo "body.list .pagination.last-page li:nth-child(2) {
    position: relative;
    z-index: 1;
}

";
        // line 1083
        echo "form label.control-label.required:after {
    content: \"\\00a0*\";
    color: ";
        // line 1085
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo ";
    font-size: 16px;
}

";
        // line 1092
        echo "body.new textarea {
    min-height: 250px;
}
body.new .field-collection-action {
    margin: -15px 0 10px;
}
body.new .field-collection-item-action {
    margin: 5px 0 0;
}

body.new #form-actions-row button,
body.new #form-actions-row a.btn {
    margin-bottom: 10px;
}
body.new .form-horizontal #form-actions-row {
    padding-left: 15px;
    padding-right: 15px;
}

";
        // line 1114
        echo "body.edit textarea {
    min-height: 250px;
}
body.edit .field-collection-action {
    margin: -15px 0 10px;
}
body.edit .field-collection-item-action {
    margin: 5px 0 0;
}

body.edit #form-actions-row button,
body.edit #form-actions-row a.btn {
    margin-bottom: 10px;
}
body.edit .form-horizontal #form-actions-row {
    padding-left: 15px;
    padding-right: 15px;
}

";
        // line 1136
        echo "body.show .form-control {
";
        // line 1137
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 1138
            echo "    background: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
            echo ";
";
        } elseif (("light" ==         // line 1139
($context["color_scheme"] ?? null))) {
            // line 1140
            echo "    background: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
            echo ";
";
        }
        // line 1142
        echo "    border: 0;
    border-radius: 0;
    box-shadow: none;
    height: auto;
}
body.show .form-control.text {
    min-height: 34px;
    max-height: 250px;
    overflow-y: auto;
}

";
        // line 1156
        echo "body.error .content-wrapper {
    align-items: center;
    display: flex;
}
body.error .error-description {
    background: ";
        // line 1161
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
    border: 1px solid ";
        // line 1162
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
        echo ";
    box-shadow: 0 0 3px ";
        // line 1163
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
        echo ";
    max-width: 96%;
    padding: 0;
}
body.error .error-description h1 {
    background: ";
        // line 1168
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "danger", array());
        echo ";
    color: ";
        // line 1169
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
    font-size: 18px;
    font-weight: bold;
    margin: 0;
    padding: 10px;
    text-transform: uppercase;
}
body.error .error-message {
    font-size: 16px;
    padding: 45px 30px;
    text-align: center;
}

";
        // line 1185
        echo "
";
        // line 1189
        echo "@media (min-width: 768px) {
    ul, ol {
        margin-left: 2em;
    }

    .main-header > .navbar {
      min-height: 50px;
    }
    .sidebar-mini.sidebar-collapse .main-header .navbar {
        margin-left: 0;
    }
    .main-header #header-logo {
        float: left;
    }
    .main-header .logo {
        font-size: 21px;
        height: 50px;
        line-height: 50px;
        text-align: left;
        transition: padding-left .3s linear;
    }
    .sidebar-mini.sidebar-collapse .main-header .logo {
        padding-left: 15px;
        width: auto; ";
        // line 1213
        echo "        transition: padding-left .3s linear;
    }

    .main-header .navbar .sidebar-toggle {
        height: 50px;
        line-height: 50px;
        position: static;
        padding: 0 12px 0 18px;
    }
    .sidebar-mini.sidebar-collapse .sidebar-toggle {
        background: rgba(0, 0, 0, 0.15);
        font-size: 18px;
        padding-left: 12px;
        width: 50px;
    }

    .navbar-custom-menu,
    .navbar-custom-menu ul.navbar-nav,
    .navbar-custom-menu ul.navbar-nav .user-menu {
        float: right;
    }
    .navbar-custom-menu {
        background: inherit;
        height: 50px;
        line-height: 50px;
        width: auto;
    }
    .navbar-custom-menu .user-menu i {
        padding-right: 4px;
    }

    .main-header .navbar .user-menu .btn {
        background: rgba(255, 255, 255, 0.1);
    }
    .main-header .navbar .user-menu .btn-group:hover .btn {
        background: rgba(255, 255, 255, 0.2);
    }

    .main-sidebar {
        padding-top: 50px;
    }

    .sidebar-mini.sidebar-collapse .sidebar-menu > li:hover > a > span {
        padding-left: 5px;
    }

    ";
        // line 1260
        echo "    body.list table {
        background: ";
        // line 1261
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
        echo ";
        border: 1px solid ";
        // line 1262
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "table_border", array());
        echo ";
    }
    body.list table thead {
        display: table-header-group;
    }
    body.list table thead th {
        background: ";
        // line 1268
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "table_header", array());
        echo ";
        padding: 0;
    }
    body.list table thead th i {
        color: ";
        // line 1272
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray", array());
        echo ";
        padding: 0 3px;
    }
    body.list table thead th a,
    body.list table thead th span {
        color: ";
        // line 1277
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "text", array());
        echo ";
        display: block;
        padding: 10px 6px;
        white-space: nowrap;
    }
    body.list table thead th a:hover {
        background: ";
        // line 1283
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
        echo ";
        text-decoration: none;
    }
    body.list table thead th.sorted,
    body.list table thead th.sorted a {
";
        // line 1288
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 1289
            echo "        background: ";
            echo ($context["brand_color"] ?? null);
            echo ";
        color: ";
            // line 1290
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
            echo ";
";
        }
        // line 1292
        echo "    }
    body.list table thead th.sorted a:hover i,
    body.list table thead th.sorted i {
";
        // line 1295
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 1296
            echo "        color: ";
            echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "white", array());
            echo ";
";
        } elseif (("light" ==         // line 1297
($context["color_scheme"] ?? null))) {
            // line 1298
            echo "        color: ";
            echo ($context["brand_color"] ?? null);
            echo ";
";
        }
        // line 1300
        echo "    }
    body.list th.boolean, body.list td.boolean,
    body.list th.toggle, body.list td.toggle,
    body.list td.image {
        text-align: center;
    }

    body.list .table thead tr th {
        border-bottom: 2px solid ";
        // line 1308
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
        echo ";
    }
    body.list .table thead tr th.sorted {
        border-bottom: 2px outset ";
        // line 1311
        echo ($context["brand_color"] ?? null);
        echo ";
    }
    ";
        // line 1314
        echo "    body.list .table thead tr th:first-child.sorted {
";
        // line 1315
        if (("dark" == ($context["color_scheme"] ?? null))) {
            // line 1316
            echo "        border-left: 1px solid ";
            echo ($context["brand_color"] ?? null);
            echo ";
        border-top: 1px solid ";
            // line 1317
            echo ($context["brand_color"] ?? null);
            echo ";
";
        }
        // line 1319
        echo "    }
    body.list .table tbody {
        border-bottom: 2px double ";
        // line 1321
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_light", array());
        echo ";
    }
    body.list table tbody tr {
        border: 0;
        display: table-row;
        margin-bottom: 0;
    }
    body.list table tbody td {
        border-bottom: 0;
        border-top: 1px solid ";
        // line 1330
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "table_row_border", array());
        echo ";
        display: table-cell;
    }
    table td:before {
        content: none;
        float: none;
    }
    body.list table tbody td.sorted {
        background: ";
        // line 1338
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
        echo ";
        border-color: ";
        // line 1339
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "table_row_border", array());
        echo ";
    }
    body.list .table tbody tr:hover td {
        background: ";
        // line 1342
        echo twig_get_attribute($this->env, $this->getSourceContext(), ($context["colors"] ?? null), "gray_lighter", array());
        echo ";
    }
    body.list table tbody td.actions a {
        margin-left: 0;
        margin-right: 10px;
    }
    body.list table td { text-align: left; }
    body.list table .text-center { text-align: center; }
    body.list table .text-left   { text-align: left; }
    body.list table .text-right  { text-align: right; }

    .field-date select + select,
    .field-time select + select,
    .field-datetime select + select {
        margin-left: 2px;
    }

    body.error .error-description {
        max-width: 550px;
    }

    .pagination > li > a,
    .pagination > li > span {
        padding: 6px 12px;
    }

    .form-inline .form-control {
        margin-bottom: 0;
    }

    body.new .form-horizontal #form-actions-row {
        margin-left: 16.66666667%;
    }

    body.edit .form-horizontal #form-actions-row {
        margin-left: 16.66666667%;
    }

    ";
        // line 1382
        echo "    .form-vertical .field-checkbox label {
        padding-top: 23px;
    }
    .form-vertical .field-group .field-checkbox label {
        padding-top: 0;
    }
}
";
    }

    public function getTemplateName()
    {
        return "@EasyAdmin/css/easyadmin.css.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  1878 => 1382,  1837 => 1342,  1831 => 1339,  1827 => 1338,  1816 => 1330,  1804 => 1321,  1800 => 1319,  1795 => 1317,  1790 => 1316,  1788 => 1315,  1785 => 1314,  1780 => 1311,  1774 => 1308,  1764 => 1300,  1758 => 1298,  1756 => 1297,  1751 => 1296,  1749 => 1295,  1744 => 1292,  1739 => 1290,  1734 => 1289,  1732 => 1288,  1724 => 1283,  1715 => 1277,  1707 => 1272,  1700 => 1268,  1691 => 1262,  1687 => 1261,  1684 => 1260,  1636 => 1213,  1611 => 1189,  1608 => 1185,  1592 => 1169,  1588 => 1168,  1580 => 1163,  1576 => 1162,  1572 => 1161,  1565 => 1156,  1552 => 1142,  1546 => 1140,  1544 => 1139,  1539 => 1138,  1537 => 1137,  1534 => 1136,  1513 => 1114,  1492 => 1092,  1485 => 1085,  1481 => 1083,  1474 => 1075,  1462 => 1063,  1458 => 1062,  1452 => 1059,  1447 => 1057,  1443 => 1056,  1439 => 1054,  1433 => 1052,  1431 => 1051,  1426 => 1050,  1424 => 1049,  1415 => 1042,  1402 => 1029,  1360 => 988,  1354 => 984,  1348 => 982,  1346 => 981,  1341 => 980,  1339 => 979,  1335 => 978,  1320 => 965,  1284 => 929,  1279 => 923,  1274 => 921,  1270 => 919,  1268 => 918,  1263 => 917,  1261 => 916,  1254 => 911,  1245 => 901,  1241 => 899,  1239 => 898,  1236 => 897,  1234 => 896,  1231 => 895,  1227 => 892,  1221 => 890,  1219 => 889,  1216 => 888,  1214 => 887,  1195 => 870,  1188 => 864,  1182 => 859,  1172 => 850,  1167 => 847,  1165 => 846,  1158 => 843,  1156 => 842,  1145 => 833,  1139 => 831,  1137 => 830,  1134 => 829,  1132 => 828,  1124 => 822,  1118 => 820,  1116 => 819,  1112 => 817,  1110 => 816,  1091 => 799,  1085 => 797,  1083 => 796,  1080 => 795,  1078 => 794,  1071 => 789,  1065 => 787,  1063 => 786,  1060 => 785,  1058 => 784,  1050 => 778,  1044 => 776,  1042 => 775,  1039 => 774,  1037 => 773,  1033 => 771,  1017 => 755,  959 => 698,  937 => 679,  910 => 654,  906 => 652,  899 => 648,  896 => 647,  887 => 639,  884 => 638,  881 => 635,  874 => 627,  867 => 621,  863 => 620,  853 => 612,  830 => 590,  826 => 589,  823 => 588,  813 => 578,  798 => 565,  794 => 564,  791 => 563,  777 => 550,  768 => 544,  762 => 541,  756 => 538,  749 => 534,  739 => 527,  728 => 519,  722 => 516,  716 => 512,  707 => 502,  701 => 498,  699 => 497,  695 => 495,  690 => 493,  684 => 491,  682 => 490,  678 => 488,  676 => 487,  671 => 484,  667 => 482,  665 => 481,  662 => 480,  660 => 479,  655 => 476,  651 => 474,  649 => 473,  646 => 472,  644 => 471,  635 => 464,  631 => 462,  629 => 461,  626 => 460,  624 => 459,  618 => 455,  613 => 453,  610 => 452,  608 => 451,  604 => 450,  599 => 449,  597 => 448,  589 => 442,  585 => 440,  583 => 439,  580 => 438,  578 => 437,  574 => 435,  568 => 433,  566 => 432,  563 => 431,  561 => 430,  554 => 425,  548 => 419,  543 => 414,  538 => 411,  536 => 410,  532 => 408,  527 => 406,  522 => 405,  520 => 404,  516 => 403,  511 => 402,  509 => 401,  496 => 391,  477 => 375,  472 => 372,  466 => 370,  464 => 369,  458 => 367,  456 => 366,  447 => 361,  443 => 359,  441 => 358,  436 => 356,  429 => 351,  399 => 322,  391 => 316,  385 => 312,  380 => 310,  369 => 302,  364 => 300,  346 => 285,  334 => 276,  329 => 274,  305 => 253,  300 => 250,  294 => 248,  292 => 247,  287 => 246,  285 => 245,  266 => 228,  260 => 223,  257 => 222,  240 => 206,  229 => 198,  225 => 197,  221 => 196,  209 => 187,  205 => 186,  201 => 185,  175 => 161,  168 => 154,  161 => 149,  151 => 143,  144 => 139,  133 => 130,  127 => 127,  124 => 126,  115 => 117,  111 => 116,  105 => 113,  101 => 112,  95 => 108,  77 => 90,  67 => 81,  60 => 77,  45 => 66,  39 => 60,  32 => 52,  29 => 45,  27 => 44,  24 => 43,  22 => 5,  19 => 4,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "@EasyAdmin/css/easyadmin.css.twig", "/home/tien/Projects/mbt-bundle/vendor/javiereguiluz/easyadmin-bundle/src/Resources/views/css/easyadmin.css.twig");
    }
}
