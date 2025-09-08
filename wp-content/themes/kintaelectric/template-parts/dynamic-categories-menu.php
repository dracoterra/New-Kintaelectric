<?php
/**
 * Template part for displaying dynamic categories menu
 * 
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Verificar que WooCommerce esté activo
if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

// Obtener categorías principales (padres) - limitado a 10 como solicitado
$parent_categories = get_terms(array(
    'taxonomy' => 'product_cat',
    'hide_empty' => true,
    'parent' => 0,
    'number' => 10
));

if ( is_wp_error( $parent_categories ) || empty( $parent_categories ) ) {
    return;
}
?>

<div class="electro-navigation row yes-home">
    <div class="departments-menu-v2">
        <div class="dropdown 
        show-dropdown">
            <a href="#" class="departments-menu-v2-title">
                <span><i class="departments-menu-v2-icon fa fa-list-ul"></i>All Departments</span>
            </a>
            <ul id="menu-all-departments-menu-1" class="dropdown-menu yamm">
                <li id="menu-item-5349"
                    class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5349">
                    <a title="Value of the Day" href="home-v2/index.htm">Value of the Day</a></li>
                <li id="menu-item-5350"
                    class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5350">
                    <a title="Top 100 Offers" href="home-v3/index.htm">Top 100 Offers</a></li>
                <li id="menu-item-5351"
                    class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5351">
                    <a title="New Arrivals" href="home-v3-full-color-background/index.htm">New
                        Arrivals</a></li>
                <li id="menu-item-5220"
                    class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5220 dropdown">
                    <a title="Computers &amp; Accessories" href="#" data-bs-toggle="dropdown-hover"
                        class="dropdown-toggle" aria-haspopup="true">Computers &#038;
                        Accessories</a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-5310"
                            class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5310">
                            <div class="yamm-content">
                                <div
                                    class="vc_row wpb_row vc_row-fluid bg-yamm-content bg-yamm-content-bottom bg-yamm-content-right">
                                    <div class="wpb_column vc_column_container vc_col-sm-12">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div
                                                    class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

                                                    <figure class="wpb_wrapper vc_figure">
                                                        <div
                                                            class="vc_single_image-wrapper   vc_box_border_grey">
                                                            <img fetchpriority="high" width="540"
                                                                height="460"
                                                                src="../../uploads/2016/03/megamenu-2-300x256.png"
                                                                class="vc_single_image-img attachment-full"
                                                                alt="" title="megamenu-2"
                                                                decoding="async"
                                                                srcset="../../uploads/2016/03/megamenu-2-300x256.png 540w, ../../uploads/2016/03/megamenu-2-300x256.png 300w"
                                                                sizes="(max-width: 540px) 100vw, 540px">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="vc_row wpb_row vc_row-fluid">
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Computers &amp;
                                                                Accessories</li>
                                                            <li><a href="#">All Computers &amp;
                                                                    Accessories</a></li>
                                                            <li><a href="#">Laptops, Desktops &amp;
                                                                    Monitors</a></li>
                                                            <li><a href="#">Printers &amp; Ink</a>
                                                            </li>
                                                            <li><a href="#">Networking &amp;
                                                                    Internet Devices</a></li>
                                                            <li><a href="#">Computer Accessories</a>
                                                            </li>
                                                            <li><a href="#">Software</a></li>
                                                            <li class="nav-divider"></li>
                                                            <li><a href="#"><span
                                                                        class="nav-text">All
                                                                        Electronics</span><span
                                                                        class="nav-subtext">Discover
                                                                        more products</span></a>
                                                            </li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Office &amp;
                                                                Stationery</li>
                                                            <li><a href="#">All Office &amp;
                                                                    Stationery</a></li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
                <li id="menu-item-5221"
                    class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5221 dropdown">
                    <a title="Cameras, Audio &amp; Video" href="#" data-bs-toggle="dropdown-hover"
                        class="dropdown-toggle" aria-haspopup="true">Cameras, Audio &#038; Video</a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-5309"
                            class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5309">
                            <div class="yamm-content">
                                <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                    <div class="wpb_column vc_column_container vc_col-sm-12">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div
                                                    class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

                                                    <figure class="wpb_wrapper vc_figure">
                                                        <div
                                                            class="vc_single_image-wrapper   vc_box_border_grey">
                                                            <img width="540" height="460"
                                                                src="../../uploads/2016/03/megamenu-3-300x256.png"
                                                                class="vc_single_image-img attachment-full"
                                                                alt="" title="megamenu-3"
                                                                decoding="async"
                                                                srcset="../../uploads/2016/03/megamenu-3-300x256.png 540w, ../../uploads/2016/03/megamenu-3-300x256.png 300w"
                                                                sizes="(max-width: 540px) 100vw, 540px">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="vc_row wpb_row vc_row-fluid">
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title"><a
                                                                    href="#">Cameras &amp;
                                                                    Photography</a></li>
                                                            <li><a href="#">Lenses</a></li>
                                                            <li><a href="#">Camera Accessories</a>
                                                            </li>
                                                            <li><a href="#">Security &amp;
                                                                    Surveillance</a></li>
                                                            <li><a href="#">Binoculars &amp;
                                                                    Telescopes</a></li>
                                                            <li><a href="#">Camcorders</a></li>
                                                            <li class="nav-divider"></li>
                                                            <li><a href="#"><span
                                                                        class="nav-text">All
                                                                        Electronics</span><span
                                                                        class="nav-subtext">Discover
                                                                        more products</span></a>
                                                            </li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Audio &amp; Video
                                                            </li>
                                                            <li><a href="#">All Audio &amp;
                                                                    Video</a></li>
                                                            <li><a href="#">Headphones &amp;
                                                                    Speakers</a></li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
                <li id="menu-item-5222"
                    class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5222 dropdown">
                    <a title="Mobiles &amp; Tablets" href="#" data-bs-toggle="dropdown-hover"
                        class="dropdown-toggle" aria-haspopup="true">Mobiles &#038; Tablets</a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-5311"
                            class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5311">
                            <div class="yamm-content">
                                <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                    <div class="wpb_column vc_column_container vc_col-sm-12">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div
                                                    class="wpb_single_image wpb_content_element vc_align_left wpb_content_element  bg-yamm-extend-outside">

                                                    <figure class="wpb_wrapper vc_figure">
                                                        <div
                                                            class="vc_single_image-wrapper   vc_box_border_grey">
                                                            <img width="540" height="495"
                                                                src="../../uploads/2016/03/megamenu--300x275.png"
                                                                class="vc_single_image-img attachment-full"
                                                                alt="" title="megamenu-"
                                                                decoding="async"
                                                                srcset="../../uploads/2016/03/megamenu--300x275.png 540w, ../../uploads/2016/03/megamenu--300x275.png 300w"
                                                                sizes="(max-width: 540px) 100vw, 540px">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="vc_row wpb_row vc_row-fluid">
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Mobiles &amp;
                                                                Tablets</li>
                                                            <li><a href="#">All Mobile Phones</a>
                                                            </li>
                                                            <li><a href="#">Smartphones</a></li>
                                                            <li><a href="#">Refurbished Mobiles</a>
                                                            </li>
                                                            <li class="nav-divider"></li>
                                                            <li><a href="#">All Mobile
                                                                    Accessories</a></li>
                                                            <li><a href="#">Cases &amp; Covers</a>
                                                            </li>
                                                            <li class="nav-divider"></li>
                                                            <li><a href="#"><span
                                                                        class="nav-text">All
                                                                        Electronics</span><span
                                                                        class="nav-subtext">Discover
                                                                        more products</span></a>
                                                            </li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title"></li>
                                                            <li><a href="#">All Tablets</a></li>
                                                            <li><a href="#">Tablet Accessories</a>
                                                            </li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
                <li id="menu-item-5223"
                    class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5223 dropdown">
                    <a title="Movies, Music &amp; Video Games" href="#"
                        data-bs-toggle="dropdown-hover" class="dropdown-toggle"
                        aria-haspopup="true">Movies, Music &#038; Video Games</a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-5312"
                            class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5312">
                            <div class="yamm-content">
                                <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                    <div class="wpb_column vc_column_container vc_col-sm-12">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div
                                                    class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

                                                    <figure class="wpb_wrapper vc_figure">
                                                        <div
                                                            class="vc_single_image-wrapper   vc_box_border_grey">
                                                            <img loading="lazy" width="540"
                                                                height="485"
                                                                src="../../uploads/2016/03/megamenu-8.png"
                                                                class="vc_single_image-img attachment-full"
                                                                alt="" title="megamenu-8"
                                                                decoding="async"
                                                                srcset="../../uploads/2016/03/megamenu-8.png 540w, ../../uploads/2016/03/megamenu-8-300x269.png 300w"
                                                                sizes="(max-width: 540px) 100vw, 540px">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="vc_row wpb_row vc_row-fluid">
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Movies &amp; TV
                                                                Shows</li>
                                                            <li><a href="#">All Movies &amp; TV
                                                                    Shows</a></li>
                                                            <li><a href="#">All English</a></li>
                                                            <li><a href="#">All Hindi</a></li>
                                                            <li class="nav-divider"></li>
                                                            <li class="nav-title">Video Games</li>
                                                            <li><a href="#">PC Games</a></li>
                                                            <li><a href="#">Consoles</a></li>
                                                            <li><a href="#">Accessories</a></li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Music</li>
                                                            <li><a href="#">All Music</a></li>
                                                            <li><a href="#">Indian Classical</a>
                                                            </li>
                                                            <li><a href="#">Musical Instruments</a>
                                                            </li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
                <li id="menu-item-5226"
                    class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5226 dropdown">
                    <a title="TV &amp; Audio" href="#" data-bs-toggle="dropdown-hover"
                        class="dropdown-toggle" aria-haspopup="true">TV &#038; Audio</a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-5314"
                            class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5314">
                            <div class="yamm-content">
                                <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                    <div class="wpb_column vc_column_container vc_col-sm-12">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div
                                                    class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

                                                    <figure class="wpb_wrapper vc_figure">
                                                        <div
                                                            class="vc_single_image-wrapper   vc_box_border_grey">
                                                            <img loading="lazy" width="540"
                                                                height="460"
                                                                src="../../uploads/2016/03/megamenu-4.png"
                                                                class="vc_single_image-img attachment-full"
                                                                alt="" title="megamenu-4"
                                                                decoding="async"
                                                                srcset="../../uploads/2016/03/megamenu-4.png 540w, ../../uploads/2016/03/megamenu-4-300x256.png 300w"
                                                                sizes="(max-width: 540px) 100vw, 540px">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="vc_row wpb_row vc_row-fluid">
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Audio &amp; Video
                                                            </li>
                                                            <li><a href="#">All Audio &amp;
                                                                    Video</a></li>
                                                            <li><a href="#">Televisions</a></li>
                                                            <li><a href="#">Headphones</a></li>
                                                            <li><a href="#">Speakers</a></li>
                                                            <li><a href="#">Audio &amp; Video
                                                                    Accessories</a></li>
                                                            <li class="nav-divider"></li>
                                                            <li><a href="#"><span
                                                                        class="nav-text">Electro
                                                                        Home Appliances</span><span
                                                                        class="nav-subtext">Available
                                                                        in select cities</span></a>
                                                            </li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Music</li>
                                                            <li><a href="#">Televisions</a></li>
                                                            <li><a href="#">Headphones</a></li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
                <li id="menu-item-5224"
                    class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5224 dropdown">
                    <a title="Watches &amp; Eyewear" href="#" data-bs-toggle="dropdown-hover"
                        class="dropdown-toggle" aria-haspopup="true">Watches &#038; Eyewear</a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-5313"
                            class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5313">
                            <div class="yamm-content">
                                <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                    <div class="wpb_column vc_column_container vc_col-sm-12">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div
                                                    class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

                                                    <figure class="wpb_wrapper vc_figure">
                                                        <div
                                                            class="vc_single_image-wrapper   vc_box_border_grey">
                                                            <img loading="lazy" width="540"
                                                                height="486"
                                                                src="../../uploads/2016/03/megamenu-7.png"
                                                                class="vc_single_image-img attachment-full"
                                                                alt="" title="megamenu-7"
                                                                decoding="async"
                                                                srcset="../../uploads/2016/03/megamenu-7.png 540w, ../../uploads/2016/03/megamenu-7-300x270.png 300w"
                                                                sizes="(max-width: 540px) 100vw, 540px">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="vc_row wpb_row vc_row-fluid">
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Watches</li>
                                                            <li><a href="#">All Watches</a></li>
                                                            <li><a href="#">Men's Watches</a></li>
                                                            <li><a href="#">Women's Watches</a></li>
                                                            <li><a href="#">Premium Watches</a></li>
                                                            <li><a href="#">Deals on Watches</a>
                                                            </li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Eyewear</li>
                                                            <li><a href="#">Men's Sunglasses</a>
                                                            </li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
                <li id="menu-item-5225"
                    class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5225 dropdown">
                    <a title="Car, Motorbike &amp; Industrial" href="#"
                        data-bs-toggle="dropdown-hover" class="dropdown-toggle"
                        aria-haspopup="true">Car, Motorbike &#038; Industrial</a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-5315"
                            class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5315">
                            <div class="yamm-content">
                                <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                    <div class="wpb_column vc_column_container vc_col-sm-12">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div
                                                    class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

                                                    <figure class="wpb_wrapper vc_figure">
                                                        <div
                                                            class="vc_single_image-wrapper   vc_box_border_grey">
                                                            <img loading="lazy" width="540"
                                                                height="523"
                                                                src="../../uploads/2016/03/megamenu-9.png"
                                                                class="vc_single_image-img attachment-full"
                                                                alt="" title="megamenu-9"
                                                                decoding="async"
                                                                srcset="../../uploads/2016/03/megamenu-9.png 540w, ../../uploads/2016/03/megamenu-9-300x291.png 300w"
                                                                sizes="(max-width: 540px) 100vw, 540px">
                                                        </div>
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="vc_row wpb_row vc_row-fluid">
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Car &amp;
                                                                Motorbike</li>
                                                            <li><a href="#">All Cars &amp; Bikes</a>
                                                            </li>
                                                            <li><a href="#">Car &amp; Bike Care</a>
                                                            </li>
                                                            <li><a href="#">Lubricants</a></li>
                                                            <li class="nav-divider"></li>
                                                            <li class="nav-title">Shop for Bike</li>
                                                            <li><a href="#">Helmets &amp; Gloves</a>
                                                            </li>
                                                            <li><a href="#">Bike Parts</a></li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpb_column vc_column_container vc_col-sm-6">
                                        <div class="vc_column-inner">
                                            <div class="wpb_wrapper">
                                                <div class="wpb_text_column wpb_content_element">
                                                    <div class="wpb_wrapper">
                                                        <ul>
                                                            <li class="nav-title">Industrial
                                                                Supplies</li>
                                                            <li><a href="#">All Industrial
                                                                    Supplies</a></li>
                                                            <li><a href="#">Lab &amp; Scientific</a>
                                                            </li>
                                                        </ul>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
                <li id="menu-item-5227"
                    class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5227 dropdown">
                    <a title="Accessories" href="#" data-bs-toggle="dropdown-hover"
                        class="dropdown-toggle" aria-haspopup="true">Accessories</a>
                    <ul role="menu" class=" dropdown-menu">
                        <li id="menu-item-5228"
                            class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5228">
                            <a title="Cases" href="#">Cases</a></li>
                        <li id="menu-item-5229"
                            class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5229">
                            <a title="Chargers" href="#">Chargers</a></li>
                        <li id="menu-item-5230"
                            class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5230">
                            <a title="Headphone Accessories" href="#">Headphone Accessories</a></li>
                        <li id="menu-item-5231"
                            class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5231">
                            <a title="Headphone Cases" href="#">Headphone Cases</a></li>
                        <li id="menu-item-5232"
                            class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5232">
                            <a title="Headphones" href="#">Headphones</a></li>
                        <li id="menu-item-5233"
                            class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5233">
                            <a title="Computer Accessories" href="#">Computer Accessories</a></li>
                        <li id="menu-item-5234"
                            class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5234">
                            <a title="Laptop Accessories" href="#">Laptop Accessories</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="secondary-nav-menu col electro-animate-dropdown position-relative">
        <ul id="menu-secondary-nav" class="secondary-nav yamm">
            <li id="menu-item-5343"
                class="highlight yamm-fw menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-5343 dropdown">
                <a title="All Pages" href="home-v2/index.htm" class="dropdown-toggle"
                    aria-haspopup="true" data-hover="dropdown">All Pages</a>
                <ul role="menu" class=" dropdown-menu">
                    <li id="menu-item-5444"
                        class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5444">
                        <div class="yamm-content">
                            <div class="vc_row wpb_row vc_row-fluid">
                                <div class="wpb_column vc_column_container vc_col-sm-3">
                                    <div class="vc_column-inner">
                                        <div class="wpb_wrapper">
                                            <div class="vc_wp_custommenu wpb_content_element">
                                                <div class="widget widget_nav_menu">
                                                    <div class="menu-pages-menu-1-container">
                                                        <ul id="menu-pages-menu-1" class="menu">
                                                            <li id="menu-item-5172"
                                                                class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5172">
                                                                <a href="#">Home Pages</a></li>
                                                            <li id="menu-item-5320"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-2139 current_page_item menu-item-5320">
                                                                <a href="index.htm"
                                                                    aria-current="page">Home v1</a>
                                                            </li>
                                                            <li id="menu-item-5318"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5318">
                                                                <a href="home-v2/index.htm">Home
                                                                    v2</a></li>
                                                            <li id="menu-item-5319"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5319">
                                                                <a href="home-v3/index.htm">Home
                                                                    v3</a></li>
                                                            <li id="menu-item-5390"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5390">
                                                                <a
                                                                    href="home-v3-full-color-background/index.htm">Home
                                                                    v3.1</a></li>
                                                            <li id="menu-item-5363"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5363">
                                                                <a href="home-v4/index.htm">Home
                                                                    v4</a></li>
                                                            <li id="menu-item-5362"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5362">
                                                                <a href="home-v5/index.htm">Home
                                                                    v5</a></li>
                                                            <li id="menu-item-5431"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5431">
                                                                <a href="home-v6/index.htm">Home
                                                                    v6</a></li>
                                                            <li id="menu-item-5430"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5430">
                                                                <a href="home-v7/index.htm">Home
                                                                    v7</a></li>
                                                            <li id="menu-item-6023"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6023">
                                                                <a href="home-v8/index.htm">Home
                                                                    v8</a></li>
                                                            <li id="menu-item-6024"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6024">
                                                                <a href="home-v9/index.htm">Home
                                                                    v9</a></li>
                                                            <li id="menu-item-6807"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6807">
                                                                <a href="home-v10/index.htm">Home
                                                                    v10</a></li>
                                                            <li id="menu-item-6806"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6806">
                                                                <a href="home-v11/index.htm">Home
                                                                    v11</a></li>
                                                            <li id="menu-item-6960"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6960">
                                                                <a href="home-v12/index.htm">Home
                                                                    v12</a></li>
                                                            <li id="menu-item-7422"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-7422">
                                                                <a href="home-v13/index.htm">Home
                                                                    v13</a></li>
                                                            <li id="menu-item-8263"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8263">
                                                                <a href="3x/index.htm">Home v14</a>
                                                            </li>
                                                            <li id="menu-item-8264"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8264">
                                                                <a href="3x/home-v15/index.htm">Home
                                                                    v15</a></li>
                                                            <li id="menu-item-8430"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8430">
                                                                <a href="4x/index.htm">Home v16</a>
                                                            </li>
                                                            <li id="menu-item-8431"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8431">
                                                                <a href="4x/home-v17/index.htm">Home
                                                                    v17</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wpb_column vc_column_container vc_col-sm-3">
                                    <div class="vc_column-inner">
                                        <div class="wpb_wrapper">
                                            <div class="vc_wp_custommenu wpb_content_element">
                                                <div class="widget widget_nav_menu">
                                                    <div class="menu-pages-menu-2-container">
                                                        <ul id="menu-pages-menu-2" class="menu">
                                                            <li id="menu-item-5178"
                                                                class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5178">
                                                                <a href="#">Shop Pages</a></li>
                                                            <li id="menu-item-5179"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5179">
                                                                <a
                                                                    href="product-category/smart-phones-tablets/index.htm#grid">Shop
                                                                    Grid</a></li>
                                                            <li id="menu-item-5180"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5180">
                                                                <a
                                                                    href="product-category/smart-phones-tablets/index.htm#grid-extended">Shop
                                                                    Grid Extended</a></li>
                                                            <li id="menu-item-5184"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5184">
                                                                <a
                                                                    href="product-category/smart-phones-tablets/index.htm#list-view">Shop
                                                                    List View</a></li>
                                                            <li id="menu-item-5185"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5185">
                                                                <a
                                                                    href="product-category/smart-phones-tablets/index.htm#list-view-small">Shop
                                                                    List View Small</a></li>
                                                            <li id="menu-item-5197"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5197">
                                                                <a
                                                                    href="electro-shop/index.htm?page_id=3030&#038;shop_columns=3&#038;shop_layout=left-sidebar">Shop
                                                                    Left Sidebar</a></li>
                                                            <li id="menu-item-5195"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5195">
                                                                <a
                                                                    href="electro-shop/index-1.htm?page_id=3030&#038;shop_columns=5&#038;shop_layout=full-width">Shop
                                                                    Full width</a></li>
                                                            <li id="menu-item-5196"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5196">
                                                                <a
                                                                    href="electro-shop/index-2.htm?page_id=3030&#038;shop_columns=3&#038;shop_layout=right-sidebar">Shop
                                                                    Right Sidebar</a></li>
                                                            <li id="menu-item-5173"
                                                                class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5173">
                                                                <a href="#">Product Categories</a>
                                                            </li>
                                                            <li id="menu-item-5175"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5175">
                                                                <a
                                                                    href="product-category/laptops-computers/index.htm?product_cat=laptops-computers&#038;cat_columns=4&#038;cat_layout=left-sidebar">4
                                                                    Column Sidebar</a></li>
                                                            <li id="menu-item-5174"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5174">
                                                                <a
                                                                    href="product-category/laptops-computers/index-1.htm?product_cat=laptops-computers&#038;cat_columns=5&#038;cat_layout=left-sidebar">5
                                                                    Column Sidebar</a></li>
                                                            <li id="menu-item-5176"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5176">
                                                                <a
                                                                    href="product-category/laptops-computers/index-2.htm?product_cat=laptops-computers&#038;cat_columns=6&#038;cat_layout=full-width">6
                                                                    Column Full width</a></li>
                                                            <li id="menu-item-5177"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5177">
                                                                <a
                                                                    href="product-category/laptops-computers/index-3.htm?product_cat=laptops-computers&#038;cat_columns=7&#038;cat_layout=full-width">7
                                                                    Columns Full width</a></li>
                                                            <li id="menu-item-8271"
                                                                class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-8271">
                                                                <a href="#">Static Pages</a></li>
                                                            <li id="menu-item-8273"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8273">
                                                                <a href="about/index.htm">About</a>
                                                            </li>
                                                            <li id="menu-item-8277"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8277">
                                                                <a href="contact-v1/index.htm">Contact
                                                                    v1</a></li>
                                                            <li id="menu-item-8276"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8276">
                                                                <a href="contact-v2/index.htm">Contact
                                                                    v2</a></li>
                                                            <li id="menu-item-8275"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8275">
                                                                <a href="faq/index.htm">FAQ</a></li>
                                                            <li id="menu-item-8278"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8278">
                                                                <a href="store-directory/index.htm">Store
                                                                    Directory</a></li>
                                                            <li id="menu-item-8274"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8274">
                                                                <a
                                                                    href="terms-and-conditions/index.htm">Terms
                                                                    and Conditions</a></li>
                                                            <li id="menu-item-8279"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8279">
                                                                <a
                                                                    href="https://electro.madrasthemes.com/?page_id=12343434">404</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wpb_column vc_column_container vc_col-sm-3">
                                    <div class="vc_column-inner">
                                        <div class="wpb_wrapper">
                                            <div class="vc_wp_custommenu wpb_content_element">
                                                <div class="widget widget_nav_menu">
                                                    <div class="menu-pages-menu-3-container">
                                                        <ul id="menu-pages-menu-3" class="menu">
                                                            <li id="menu-item-5288"
                                                                class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5288">
                                                                <a href="#">Mobile Home Pages</a>
                                                            </li>
                                                            <li id="menu-item-5289"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5289">
                                                                <a target="_blank"
                                                                    href="https://transvelo.github.io/electro/mobile.html">Home
                                                                    v1</a></li>
                                                            <li id="menu-item-5290"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5290">
                                                                <a target="_blank"
                                                                    href="https://transvelo.github.io/electro/mobile.html">Home
                                                                    v2</a></li>
                                                            <li id="menu-item-5188"
                                                                class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5188">
                                                                <a href="#">Single Product Pages</a>
                                                            </li>
                                                            <li id="menu-item-5338"
                                                                class="menu-item menu-item-type-post_type menu-item-object-product menu-item-5338">
                                                                <a
                                                                    href="product/ultra-wireless-s50-headphones-s50-with-bluetooth/index.htm">Single
                                                                    Product Extended</a></li>
                                                            <li id="menu-item-5337"
                                                                class="menu-item menu-item-type-post_type menu-item-object-product menu-item-5337">
                                                                <a
                                                                    href="product/ultra-wireless-s50-headphones-s50-with-bluetooth-2/index.htm">Single
                                                                    Product Fullwidth</a></li>
                                                            <li id="menu-item-5336"
                                                                class="menu-item menu-item-type-post_type menu-item-object-product menu-item-5336">
                                                                <a
                                                                    href="product/ultra-wireless-s50-headphones-s50-with-bluetooth-3/index.htm">Single
                                                                    Product Sidebar</a></li>
                                                            <li id="menu-item-5187"
                                                                class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5187">
                                                                <a href="#">WooCommerce Pages</a>
                                                            </li>
                                                            <li id="menu-item-5329"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5329">
                                                                <a href="shop/index.htm">Shop</a>
                                                            </li>
                                                            <li id="menu-item-5326"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5326">
                                                                <a href="cart/index.htm">Cart</a>
                                                            </li>
                                                            <li id="menu-item-5327"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5327">
                                                                <a
                                                                    href="cart/index.htm">Checkout</a>
                                                            </li>
                                                            <li id="menu-item-5328"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5328">
                                                                <a href="my-account/index.htm">My
                                                                    Account</a></li>
                                                            <li id="menu-item-5330"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5330">
                                                                <a
                                                                    href="track-your-order/index.htm">Track
                                                                    your Order</a></li>
                                                            <li id="menu-item-5359"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5359">
                                                                <a
                                                                    href="compare/index.htm">Compare</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wpb_column vc_column_container vc_col-sm-3">
                                    <div class="vc_column-inner">
                                        <div class="wpb_wrapper">
                                            <div class="vc_wp_custommenu wpb_content_element">
                                                <div class="widget widget_nav_menu">
                                                    <div class="menu-pages-menu-4-container">
                                                        <ul id="menu-pages-menu-4" class="menu">
                                                            <li id="menu-item-5186"
                                                                class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5186">
                                                                <a href="#">Blog Pages</a></li>
                                                            <li id="menu-item-5331"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5331">
                                                                <a href="blog-v1/index.htm">Blog
                                                                    v1</a></li>
                                                            <li id="menu-item-5332"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5332">
                                                                <a href="blog-v3/index.htm">Blog
                                                                    v3</a></li>
                                                            <li id="menu-item-5333"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5333">
                                                                <a href="blog-v2/index.htm">Blog
                                                                    v2</a></li>
                                                            <li id="menu-item-5334"
                                                                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5334">
                                                                <a href="blog-full-width/index.htm">Blog
                                                                    Full Width</a></li>
                                                            <li id="menu-item-5335"
                                                                class="menu-item menu-item-type-post_type menu-item-object-post menu-item-5335">
                                                                <a
                                                                    href="blog/2016/03/01/robot-wars-now-closed/index.htm">Single
                                                                    Blog Post</a></li>
                                                            <li id="menu-item-5292"
                                                                class="nav-title menu-item menu-item-type-post_type menu-item-object-page menu-item-5292">
                                                                <a href="shop/index.htm">Shop
                                                                    Columns</a></li>
                                                            <li id="menu-item-5189"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5189">
                                                                <a
                                                                    href="electro-shop/index-3.htm?page_id=3030&#038;shop_columns=7&#038;shop_layout=full-width">7
                                                                    Columns Full width</a></li>
                                                            <li id="menu-item-5190"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5190">
                                                                <a
                                                                    href="electro-shop/index-4.htm?page_id=3030&#038;shop_columns=6&#038;shop_layout=full-width">6
                                                                    Columns Full width</a></li>
                                                            <li id="menu-item-5191"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5191">
                                                                <a
                                                                    href="electro-shop/index-5.htm?page_id=3030&#038;shop_columns=5&#038;shop_layout=left-sidebar">5
                                                                    Columns Sidebar</a></li>
                                                            <li id="menu-item-5192"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5192">
                                                                <a
                                                                    href="electro-shop/index-6.htm?page_id=3030&#038;shop_columns=4&#038;shop_layout=left-sidebar">4
                                                                    Columns Sidebar</a></li>
                                                            <li id="menu-item-5193"
                                                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5193">
                                                                <a
                                                                    href="electro-shop/index.htm?page_id=3030&#038;shop_columns=3&#038;shop_layout=left-sidebar">3
                                                                    Columns Sidebar</a></li>
                                                            <li id="menu-item-5291"
                                                                class="nav-title menu-item menu-item-type-custom menu-item-object-custom menu-item-5291">
                                                                <a target="_blank"
                                                                    href="store/multivendor/index.htm">Vendor
                                                                    Demo</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </li>
            <li id="menu-item-5344"
                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5344"><a
                    title="Featured Brands" href="home-v3/index.htm">Featured Brands</a></li>
            <li id="menu-item-5345"
                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5345"><a
                    title="Trending Styles" href="home-v3-full-color-background/index.htm">Trending
                    Styles</a></li>
            <li id="menu-item-5341"
                class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5341"><a
                    title="Gift Cards" href="blog-v1/index.htm">Gift Cards</a></li>
            <li id="menu-item-5342"
                class="pull-end menu-item menu-item-type-post_type menu-item-object-page menu-item-5342">
                <a title="Free Shipping on Orders $50+" href="blog-v2/index.htm">Free Shipping on
                    Orders $50+</a></li>
        </ul>
    </div>
</div>