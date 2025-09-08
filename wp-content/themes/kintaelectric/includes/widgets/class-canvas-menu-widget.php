<?php
/**
 * KintaElectric Canvas Menu Widget
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class KintaElectric_Canvas_Menu_Widget extends WP_Widget {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            'kintaelectric_canvas_menu_widget', // Base ID
            esc_html__( 'KintaElectric Canvas Menu', 'kintaelectric' ), // Name
            array( 'description' => esc_html__( 'Displays the off-canvas navigation menu.', 'kintaelectric' ) ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        ?>
        <ul id="menu-all-departments-menu" class="nav nav-inline yamm">
            <li id="menu-item-5349"
                class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5349">
                <a title="Value of the Day" href="home-v2/index.htm">Value of the Day</a>
            </li>
            <li id="menu-item-5350"
                class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5350">
                <a title="Top 100 Offers" href="home-v3/index.htm">Top 100 Offers</a></li>
            <li id="menu-item-5351"
                class="highlight menu-item menu-item-type-post_type menu-item-object-page menu-item-5351">
                <a title="New Arrivals" href="home-v3-full-color-background/index.htm">New
                    Arrivals</a></li>
            <li id="menu-item-5220"
                class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5220 dropdown">
                <a title="Computers &amp; Accessories" href="#" data-bs-toggle="dropdown"
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
                                        <div
                                            class="wpb_single_image wpb_content_element vc_align_left wpb_content_element">

                                            <figure class="wpb_wrapper vc_figure">
                                                <div
                                                    class="vc_single_image-wrapper   vc_box_border_grey">
                                                    <img fetchpriority="high"
                                                        width="540" height="460"
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
                            <div class="vc_row wpb_row vc_row-fluid">
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title">Computers
                                                        &amp; Accessories</li>
                                                    <li><a href="#">All Computers &amp;
                                                            Accessories</a></li>
                                                    <li><a href="#">Laptops, Desktops
                                                            &amp; Monitors</a></li>
                                                    <li><a href="#">Printers &amp;
                                                            Ink</a></li>
                                                    <li><a href="#">Networking &amp;
                                                            Internet Devices</a></li>
                                                    <li><a href="#">Computer
                                                            Accessories</a></li>
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
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
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
                    </li>
                </ul>
            </li>
            <li id="menu-item-5221"
                class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5221 dropdown">
                <a title="Cameras, Audio &amp; Video" href="#" data-bs-toggle="dropdown"
                    class="dropdown-toggle" aria-haspopup="true">Cameras, Audio &#038;
                    Video</a>
                <ul role="menu" class=" dropdown-menu">
                    <li id="menu-item-5309"
                        class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5309">
                        <div class="yamm-content">
                            <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                <div class="wpb_column vc_column_container vc_col-sm-12">
                                    <div class="vc_column-inner">
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
                            <div class="vc_row wpb_row vc_row-fluid">
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title"><a
                                                            href="#">Cameras &amp;
                                                            Photography</a></li>
                                                    <li><a href="#">Lenses</a></li>
                                                    <li><a href="#">Camera
                                                            Accessories</a></li>
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
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title">Audio &amp;
                                                        Video</li>
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
                    </li>
                </ul>
            </li>
            <li id="menu-item-5222"
                class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5222 dropdown">
                <a title="Mobiles &amp; Tablets" href="#" data-bs-toggle="dropdown"
                    class="dropdown-toggle" aria-haspopup="true">Mobiles &#038; Tablets</a>
                <ul role="menu" class=" dropdown-menu">
                    <li id="menu-item-5311"
                        class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5311">
                        <div class="yamm-content">
                            <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                <div class="wpb_column vc_column_container vc_col-sm-12">
                                    <div class="vc_column-inner">
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
                            <div class="vc_row wpb_row vc_row-fluid">
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title">Mobiles &amp;
                                                        Tablets</li>
                                                    <li><a href="#">All Mobile
                                                            Phones</a></li>
                                                    <li><a href="#">Smartphones</a></li>
                                                    <li><a href="#">Refurbished
                                                            Mobiles</a></li>
                                                    <li class="nav-divider"></li>
                                                    <li><a href="#">All Mobile
                                                            Accessories</a></li>
                                                    <li><a href="#">Cases &amp;
                                                            Covers</a></li>
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
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title"></li>
                                                    <li><a href="#">All Tablets</a></li>
                                                    <li><a href="#">Tablet
                                                            Accessories</a></li>
                                                </ul>

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
                    data-bs-toggle="dropdown" class="dropdown-toggle"
                    aria-haspopup="true">Movies, Music &#038; Video Games</a>
                <ul role="menu" class=" dropdown-menu">
                    <li id="menu-item-5312"
                        class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5312">
                        <div class="yamm-content">
                            <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                <div class="wpb_column vc_column_container vc_col-sm-12">
                                    <div class="vc_column-inner">
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
                            <div class="vc_row wpb_row vc_row-fluid">
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title">Movies &amp;
                                                        TV Shows</li>
                                                    <li><a href="#">All Movies &amp; TV
                                                            Shows</a></li>
                                                    <li><a href="#">All English</a></li>
                                                    <li><a href="#">All Hindi</a></li>
                                                    <li class="nav-divider"></li>
                                                    <li class="nav-title">Video Games
                                                    </li>
                                                    <li><a href="#">PC Games</a></li>
                                                    <li><a href="#">Consoles</a></li>
                                                    <li><a href="#">Accessories</a></li>
                                                </ul>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title">Music</li>
                                                    <li><a href="#">All Music</a></li>
                                                    <li><a href="#">Indian Classical</a>
                                                    </li>
                                                    <li><a href="#">Musical
                                                            Instruments</a></li>
                                                </ul>

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
                <a title="TV &amp; Audio" href="#" data-bs-toggle="dropdown"
                    class="dropdown-toggle" aria-haspopup="true">TV &#038; Audio</a>
                <ul role="menu" class=" dropdown-menu">
                    <li id="menu-item-5314"
                        class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5314">
                        <div class="yamm-content">
                            <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                <div class="wpb_column vc_column_container vc_col-sm-12">
                                    <div class="vc_column-inner">
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
                            <div class="vc_row wpb_row vc_row-fluid">
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title">Audio &amp;
                                                        Video</li>
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
                                                                Home
                                                                Appliances</span><span
                                                                class="nav-subtext">Available
                                                                in select
                                                                cities</span></a></li>
                                                </ul>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
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
                    </li>
                </ul>
            </li>
            <li id="menu-item-5224"
                class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5224 dropdown">
                <a title="Watches &amp; Eyewear" href="#" data-bs-toggle="dropdown"
                    class="dropdown-toggle" aria-haspopup="true">Watches &#038; Eyewear</a>
                <ul role="menu" class=" dropdown-menu">
                    <li id="menu-item-5313"
                        class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5313">
                        <div class="yamm-content">
                            <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                <div class="wpb_column vc_column_container vc_col-sm-12">
                                    <div class="vc_column-inner">
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
                            <div class="vc_row wpb_row vc_row-fluid">
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title">Watches</li>
                                                    <li><a href="#">All Watches</a></li>
                                                    <li><a href="#">Men's Watches</a>
                                                    </li>
                                                    <li><a href="#">Women's Watches</a>
                                                    </li>
                                                    <li><a href="#">Premium Watches</a>
                                                    </li>
                                                    <li><a href="#">Deals on Watches</a>
                                                    </li>
                                                </ul>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
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
                    </li>
                </ul>
            </li>
            <li id="menu-item-5225"
                class="yamm-tfw menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-5225 dropdown">
                <a title="Car, Motorbike &amp; Industrial" href="#"
                    data-bs-toggle="dropdown" class="dropdown-toggle"
                    aria-haspopup="true">Car, Motorbike &#038; Industrial</a>
                <ul role="menu" class=" dropdown-menu">
                    <li id="menu-item-5315"
                        class="menu-item menu-item-type-post_type menu-item-object-mas_static_content menu-item-5315">
                        <div class="yamm-content">
                            <div class="vc_row wpb_row vc_row-fluid bg-yamm-content">
                                <div class="wpb_column vc_column_container vc_col-sm-12">
                                    <div class="vc_column-inner">
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
                            <div class="vc_row wpb_row vc_row-fluid">
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title">Car &amp;
                                                        Motorbike</li>
                                                    <li><a href="#">All Cars &amp;
                                                            Bikes</a></li>
                                                    <li><a href="#">Car &amp; Bike
                                                            Care</a></li>
                                                    <li><a href="#">Lubricants</a></li>
                                                    <li class="nav-divider"></li>
                                                    <li><a href="#">Shop for Bike
                                                    </li>
                                                    <li><a href="#">Helmets &amp;
                                                            Gloves</a></li>
                                                    <li><a href="#">Bike Parts</a></li>
                                                </ul>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wpb_column vc_column_container vc_col-sm-6">
                                    <div class="vc_column-inner">
                                        <div
                                            class="wpb_text_column wpb_content_element">
                                            <div class="wpb_wrapper">
                                                <ul>
                                                    <li class="nav-title">Industrial
                                                        Supplies</li>
                                                    <li><a href="#">All Industrial
                                                            Supplies</a></li>
                                                    <li><a href="#">Lab &amp;
                                                            Scientific</a></li>
                                                </ul>

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
                <a title="Accessories" href="#" data-bs-toggle="dropdown"
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
                        <a title="Headphone Accessories" href="#">Headphone Accessories</a>
                    </li>
                    <li id="menu-item-5231"
                        class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5231">
                        <a title="Headphone Cases" href="#">Headphone Cases</a></li>
                    <li id="menu-item-5232"
                        class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5232">
                        <a title="Headphones" href="#">Headphones</a></li>
                    <li id="menu-item-5233"
                        class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5233">
                        <a title="Computer Accessories" href="#">Computer Accessories</a>
                    </li>
                    <li id="menu-item-5234"
                        class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5234">
                        <a title="Laptop Accessories" href="#">Laptop Accessories</a></li>
                </ul>
            </li>
        </ul>
        <?php
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        // No options for this widget, as it displays static content.
        echo '<p>' . esc_html__( 'This widget displays the static off-canvas navigation menu.', 'kintaelectric' ) . '</p>';
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        return $instance;
    }
}
