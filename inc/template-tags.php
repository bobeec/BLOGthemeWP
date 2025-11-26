<?php
/**
 * テンプレートタグ
 *
 * @package BLOGthemeWP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 投稿日
 */
function blogthemewp_posted_on() {
    if ( ! blogthemewp_show( 'show_post_date' ) ) return;
    
    echo '<time class="entry-date" datetime="' . esc_attr( get_the_date( 'c' ) ) . '">';
    echo esc_html( get_the_date() );
    echo '</time>';
}

/**
 * 著者名
 */
function blogthemewp_posted_by() {
    if ( ! blogthemewp_show( 'show_author' ) ) return;
    
    echo '<span class="author">';
    echo '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">';
    echo esc_html( get_the_author() );
    echo '</a></span>';
}

/**
 * 読了時間
 */
function blogthemewp_reading_time_display() {
    if ( ! blogthemewp_show( 'show_reading_time' ) ) return;
    
    $time = blogthemewp_reading_time();
    echo '<span class="reading-time">' . sprintf( __( '%s分で読める', 'blogthemewp' ), $time ) . '</span>';
}

/**
 * カテゴリー・タグ
 */
function blogthemewp_entry_footer() {
    if ( get_post_type() !== 'post' ) return;
    
    if ( blogthemewp_show( 'show_categories' ) ) {
        $cats = get_the_category_list( ', ' );
        if ( $cats ) {
            echo '<span class="cat-links">' . $cats . '</span>';
        }
    }
    
    if ( blogthemewp_show( 'show_tags' ) ) {
        $tags = get_the_tag_list( '', ', ' );
        if ( $tags ) {
            echo '<span class="tags-links">' . $tags . '</span>';
        }
    }
}

/**
 * 著者ボックス
 */
function blogthemewp_author_box() {
    if ( ! blogthemewp_show( 'show_author_box' ) ) return;
    
    $bio = get_the_author_meta( 'description' );
    ?>
    <div class="author-box">
        <div class="author-avatar">
            <?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
        </div>
        <div class="author-info">
            <h4 class="author-name">
                <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
                    <?php the_author(); ?>
                </a>
            </h4>
            <?php if ( $bio ) : ?>
                <p class="author-bio"><?php echo esc_html( $bio ); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * 前後の記事リンク
 */
function blogthemewp_post_navigation() {
    if ( ! blogthemewp_show( 'show_post_nav' ) ) return;
    
    $prev = get_previous_post();
    $next = get_next_post();
    
    if ( ! $prev && ! $next ) return;
    ?>
    <nav class="post-navigation">
        <?php if ( $prev ) : ?>
        <div class="nav-previous">
            <a href="<?php echo esc_url( get_permalink( $prev ) ); ?>">
                <span class="nav-label"><?php _e( '前の記事', 'blogthemewp' ); ?></span>
                <span class="nav-title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
            </a>
        </div>
        <?php endif; ?>
        
        <?php if ( $next ) : ?>
        <div class="nav-next">
            <a href="<?php echo esc_url( get_permalink( $next ) ); ?>">
                <span class="nav-label"><?php _e( '次の記事', 'blogthemewp' ); ?></span>
                <span class="nav-title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
            </a>
        </div>
        <?php endif; ?>
    </nav>
    <?php
}

/**
 * ページネーション
 */
function blogthemewp_pagination() {
    the_posts_pagination( array(
        'mid_size'  => 1,
        'prev_text' => '&larr;',
        'next_text' => '&rarr;',
    ) );
}

/**
 * パンくずリスト（構造化データ付き）
 */
function blogthemewp_breadcrumb() {
    if ( ! blogthemewp_show( 'show_breadcrumb' ) ) return;
    if ( is_front_page() ) return;
    
    $items = array();
    $position = 1;
    
    // ホーム
    $items[] = array(
        'name' => __( 'ホーム', 'blogthemewp' ),
        'url'  => home_url( '/' ),
    );
    
    if ( is_single() ) {
        // カテゴリー（最初の1つ）
        $categories = get_the_category();
        if ( ! empty( $categories ) ) {
            $cat = $categories[0];
            // 親カテゴリーがあれば追加
            if ( $cat->parent ) {
                $parent = get_category( $cat->parent );
                $items[] = array(
                    'name' => $parent->name,
                    'url'  => get_category_link( $parent->term_id ),
                );
            }
            $items[] = array(
                'name' => $cat->name,
                'url'  => get_category_link( $cat->term_id ),
            );
        }
        // 現在の記事
        $items[] = array(
            'name' => get_the_title(),
            'url'  => '',
        );
    } elseif ( is_page() ) {
        // 親ページがあれば追加
        global $post;
        if ( $post->post_parent ) {
            $ancestors = array_reverse( get_post_ancestors( $post->ID ) );
            foreach ( $ancestors as $ancestor_id ) {
                $items[] = array(
                    'name' => get_the_title( $ancestor_id ),
                    'url'  => get_permalink( $ancestor_id ),
                );
            }
        }
        // 現在のページ
        $items[] = array(
            'name' => get_the_title(),
            'url'  => '',
        );
    } elseif ( is_category() ) {
        $cat = get_queried_object();
        if ( $cat->parent ) {
            $parent = get_category( $cat->parent );
            $items[] = array(
                'name' => $parent->name,
                'url'  => get_category_link( $parent->term_id ),
            );
        }
        $items[] = array(
            'name' => $cat->name,
            'url'  => '',
        );
    } elseif ( is_tag() ) {
        $items[] = array(
            'name' => single_tag_title( '', false ),
            'url'  => '',
        );
    } elseif ( is_date() ) {
        if ( is_year() ) {
            $items[] = array(
                'name' => get_the_date( 'Y年' ),
                'url'  => '',
            );
        } elseif ( is_month() ) {
            $items[] = array(
                'name' => get_the_date( 'Y年n月' ),
                'url'  => '',
            );
        } elseif ( is_day() ) {
            $items[] = array(
                'name' => get_the_date( 'Y年n月j日' ),
                'url'  => '',
            );
        }
    } elseif ( is_author() ) {
        $items[] = array(
            'name' => get_the_author(),
            'url'  => '',
        );
    } elseif ( is_search() ) {
        $items[] = array(
            'name' => sprintf( __( '「%s」の検索結果', 'blogthemewp' ), get_search_query() ),
            'url'  => '',
        );
    } elseif ( is_404() ) {
        $items[] = array(
            'name' => __( 'ページが見つかりません', 'blogthemewp' ),
            'url'  => '',
        );
    }
    
    // HTML出力
    echo '<nav class="breadcrumb" aria-label="' . esc_attr__( 'パンくずリスト', 'blogthemewp' ) . '">';
    echo '<ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';
    
    foreach ( $items as $index => $item ) {
        $position = $index + 1;
        $is_last = ( $index === count( $items ) - 1 );
        
        echo '<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        
        if ( ! $is_last && $item['url'] ) {
            echo '<a href="' . esc_url( $item['url'] ) . '" itemprop="item">';
            echo '<span itemprop="name">' . esc_html( $item['name'] ) . '</span>';
            echo '</a>';
        } else {
            echo '<span itemprop="name">' . esc_html( $item['name'] ) . '</span>';
        }
        
        echo '<meta itemprop="position" content="' . esc_attr( $position ) . '">';
        echo '</li>';
        
        if ( ! $is_last ) {
            echo '<li class="breadcrumb-separator" aria-hidden="true">/</li>';
        }
    }
    
    echo '</ol>';
    echo '</nav>';
}

/**
 * 更新日表示
 */
function blogthemewp_modified_date() {
    if ( ! blogthemewp_show( 'show_modified_date' ) ) return;
    if ( get_the_date() === get_the_modified_date() ) return;
    
    echo '<time class="entry-modified" datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">';
    echo esc_html__( '更新：', 'blogthemewp' ) . esc_html( get_the_modified_date() );
    echo '</time>';
}
