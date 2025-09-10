<?php
/**
 * Widget Breadcrumb KintaElectric
 * 
 * @package KintaElectronicElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Widget Breadcrumb KintaElectric
 */
class KEE_Breadcrumb_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'breadcrumb-kintaelectric';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return esc_html__('Breadcrumb KintaElectric', 'kinta-electronic-elementor');
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'eicon-navigation-horizontal';
    }

    /**
     * Get widget categories.
     */
    public function get_categories() {
        return ['kinta-electric'];
    }

    /**
     * Get widget keywords.
     */
    public function get_keywords() {
        return ['breadcrumb', 'navigation', 'path', 'kintaelectric', 'menu'];
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        // No controls needed - this widget is static
    }

    /**
     * Generate breadcrumb trail automatically
     */
    private function get_breadcrumb_trail() {
        $breadcrumbs = array();
        
        // Always start with Home
        $breadcrumbs[] = array(
            'text' => esc_html__('Home', 'kinta-electronic-elementor'),
            'url' => home_url(),
            'is_active' => false
        );
        
        if (is_front_page()) {
            // If we're on the home page, mark it as active
            $breadcrumbs[0]['is_active'] = true;
        } elseif (is_home()) {
            // Blog page
            $breadcrumbs[] = array(
                'text' => get_the_title(get_option('page_for_posts')),
                'url' => '',
                'is_active' => true
            );
        } elseif (is_single()) {
            // Single post
            if (has_category()) {
                $categories = get_the_category();
                $category = $categories[0];
                $breadcrumbs[] = array(
                    'text' => $category->name,
                    'url' => get_category_link($category->term_id),
                    'is_active' => false
                );
            }
            $breadcrumbs[] = array(
                'text' => get_the_title(),
                'url' => '',
                'is_active' => true
            );
        } elseif (is_page()) {
            // Page
            $ancestors = array_reverse(get_post_ancestors(get_the_ID()));
            foreach ($ancestors as $ancestor_id) {
                $breadcrumbs[] = array(
                    'text' => get_the_title($ancestor_id),
                    'url' => get_permalink($ancestor_id),
                    'is_active' => false
                );
            }
            $breadcrumbs[] = array(
                'text' => get_the_title(),
                'url' => '',
                'is_active' => true
            );
        } elseif (is_category()) {
            // Category archive
            $breadcrumbs[] = array(
                'text' => single_cat_title('', false),
                'url' => '',
                'is_active' => true
            );
        } elseif (is_tag()) {
            // Tag archive
            $breadcrumbs[] = array(
                'text' => single_tag_title('', false),
                'url' => '',
                'is_active' => true
            );
        } elseif (is_author()) {
            // Author archive
            $breadcrumbs[] = array(
                'text' => get_the_author(),
                'url' => '',
                'is_active' => true
            );
        } elseif (is_date()) {
            // Date archive
            if (is_year()) {
                $breadcrumbs[] = array(
                    'text' => get_the_date('Y'),
                    'url' => '',
                    'is_active' => true
                );
            } elseif (is_month()) {
                $breadcrumbs[] = array(
                    'text' => get_the_date('F Y'),
                    'url' => '',
                    'is_active' => true
                );
            } elseif (is_day()) {
                $breadcrumbs[] = array(
                    'text' => get_the_date(),
                    'url' => '',
                    'is_active' => true
                );
            }
        } elseif (is_search()) {
            // Search results
            $breadcrumbs[] = array(
                'text' => sprintf(esc_html__('Search Results for: %s', 'kinta-electronic-elementor'), get_search_query()),
                'url' => '',
                'is_active' => true
            );
        } elseif (is_404()) {
            // 404 page
            $breadcrumbs[] = array(
                'text' => esc_html__('Page Not Found', 'kinta-electronic-elementor'),
                'url' => '',
                'is_active' => true
            );
        }
        
        return $breadcrumbs;
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $breadcrumbs = $this->get_breadcrumb_trail();
        
        ?>
        <!-- breadcrumb -->
        <div class="bg-gray-13 bg-md-transparent">
            <div class="container">
                <!-- breadcrumb -->
                <div class="my-md-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3 flex-nowrap flex-xl-wrap overflow-auto overflow-xl-visble">
                            <?php foreach ($breadcrumbs as $index => $breadcrumb) : ?>
                                <li class="breadcrumb-item flex-shrink-0 flex-xl-shrink-1 <?php echo $breadcrumb['is_active'] ? 'active' : ''; ?>" 
                                    <?php echo $breadcrumb['is_active'] ? 'aria-current="page"' : ''; ?>>
                                    <?php if ($breadcrumb['is_active'] || empty($breadcrumb['url'])) : ?>
                                        <?php echo esc_html($breadcrumb['text']); ?>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url($breadcrumb['url']); ?>">
                                            <?php echo esc_html($breadcrumb['text']); ?>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                </div>
                <!-- End breadcrumb -->
            </div>
        </div>
        <!-- End breadcrumb -->
        <?php
    }

    /**
     * Render widget output in the editor.
     */
    protected function content_template() {
        ?>
        <!-- breadcrumb -->
        <div class="bg-gray-13 bg-md-transparent">
            <div class="container">
                <!-- breadcrumb -->
                <div class="my-md-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3 flex-nowrap flex-xl-wrap overflow-auto overflow-xl-visble">
                            <li class="breadcrumb-item flex-shrink-0 flex-xl-shrink-1">
                                <a href="#">Home</a>
                            </li>
                            <li class="breadcrumb-item flex-shrink-0 flex-xl-shrink-1">
                                <a href="#">Category</a>
                            </li>
                            <li class="breadcrumb-item flex-shrink-0 flex-xl-shrink-1 active" aria-current="page">
                                Current Page
                            </li>
                        </ol>
                    </nav>
                </div>
                <!-- End breadcrumb -->
            </div>
        </div>
        <!-- End breadcrumb -->
        <?php
    }
}
