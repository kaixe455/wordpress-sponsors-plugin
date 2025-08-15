<?php
/*
Plugin Name: Mis Patrocinadores
Description: Añade y gestiona patrocinadores con nombre, descripción breve, descripción larga, logo, enlace y color de fondo.
Version: 1.2
Author: Tu Nombre
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Registrar CPT Patrocinadores
function mp_registrar_cpt() {
    $labels = array(
        'name'               => 'Patrocinadores',
        'singular_name'      => 'Patrocinador',
        'menu_name'          => 'Patrocinadores',
        'add_new'            => 'Añadir nuevo',
        'add_new_item'       => 'Añadir nuevo patrocinador',
        'edit_item'          => 'Editar patrocinador',
        'new_item'           => 'Nuevo patrocinador',
        'view_item'          => 'Ver patrocinador',
        'search_items'       => 'Buscar patrocinadores',
        'not_found'          => 'No se han encontrado patrocinadores',
        'not_found_in_trash' => 'No hay patrocinadores en la papelera'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'supports'           => array( 'title', 'editor', 'thumbnail' ), // nombre, descripción larga, logo
        'menu_icon'          => 'dashicons-groups',
    );

    register_post_type( 'patrocinador', $args );
}
add_action( 'init', 'mp_registrar_cpt' );

// Campos extra: descripción breve, enlace, color de fondo
function mp_agregar_meta_boxes() {
    add_meta_box( 'mp_info_patrocinador', 'Información del Patrocinador', 'mp_render_meta_box', 'patrocinador', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'mp_agregar_meta_boxes' );

function mp_render_meta_box( $post ) {
    $descripcion_breve = get_post_meta( $post->ID, '_mp_descripcion_breve', true );
    $enlace = get_post_meta( $post->ID, '_mp_enlace', true );
    $color = get_post_meta( $post->ID, '_mp_color_fondo', true );

    ?>
    <p>
        <label for="mp_descripcion_breve"><strong>Descripción breve:</strong></label><br>
        <input type="text" name="mp_descripcion_breve" id="mp_descripcion_breve" value="<?php echo esc_attr( $descripcion_breve ); ?>" style="width:100%;">
    </p>
    <p>
        <label for="mp_enlace"><strong>Enlace a la web:</strong></label><br>
        <input type="url" name="mp_enlace" id="mp_enlace" value="<?php echo esc_attr( $enlace ); ?>" style="width:100%;">
    </p>
    <p>
        <label for="mp_color_fondo"><strong>Color de fondo:</strong></label><br>
        <select name="mp_color_fondo" id="mp_color_fondo" style="width:100%;">
            <option value="white-bg" <?php selected( $color, 'white-bg' ); ?>>Blanco</option>
            <option value="blue-bg" <?php selected( $color, 'blue-bg' ); ?>>Azul</option>
        </select>
    </p>
    <?php
}

function mp_guardar_meta_boxes( $post_id ) {
    if ( isset( $_POST['mp_descripcion_breve'] ) ) {
        update_post_meta( $post_id, '_mp_descripcion_breve', sanitize_text_field( $_POST['mp_descripcion_breve'] ) );
    }
    if ( isset( $_POST['mp_enlace'] ) ) {
        update_post_meta( $post_id, '_mp_enlace', esc_url_raw( $_POST['mp_enlace'] ) );
    }
    if ( isset( $_POST['mp_color_fondo'] ) ) {
        update_post_meta( $post_id, '_mp_color_fondo', sanitize_text_field( $_POST['mp_color_fondo'] ) );
    }
}
add_action( 'save_post', 'mp_guardar_meta_boxes' );

// Shortcode [patrocinadores]
function mp_mostrar_patrocinadores() {
    $query = new WP_Query( array(
        'post_type'      => 'patrocinador',
        'posts_per_page' => -1
    ) );

    if ( ! $query->have_posts() ) return '<p>No hay patrocinadores aún.</p>';

    wp_enqueue_style( 'mp-styles', plugins_url( 'css/styles.css', __FILE__ ) );

    $output = '';
    while ( $query->have_posts() ) {
        $query->the_post();
        $descripcion_breve = get_post_meta( get_the_ID(), '_mp_descripcion_breve', true );
        $enlace = get_post_meta( get_the_ID(), '_mp_enlace', true );
        $color = get_post_meta( get_the_ID(), '_mp_color_fondo', true ) ?: 'white-bg';
        $logo = get_the_post_thumbnail( get_the_ID(), 'medium', array( 'alt' => get_the_title() ) );
        $nombre = get_the_title();
        $descripcion_larga = get_the_content();

        $output .= '<div class="sponsor-section ' . esc_attr( $color ) . '">';
        $output .= '    <div class="sponsor">';
        if ( $enlace ) {
            $output .= '        <a href="' . esc_url( $enlace ) . '" target="_blank">' . $logo . '</a>';
        } else {
            $output .= $logo;
        }
        $output .= '        <div class="sponsor-description">';
        $output .= '            <h3>' . esc_html( $nombre ) . '</h3>';
        if ( $descripcion_breve ) {
            $output .= '            <h4>' . esc_html( $descripcion_breve ) . '</h4>';
        }
        if ( $descripcion_larga ) {
            $output .= '            <p>' . esc_html( $descripcion_larga ) . '</p>';
        }
        if ( $enlace ) {
            $output .= '            <a href="' . esc_url( $enlace ) . '" target="_blank" class="cta-button">Descubrir</a>';
        }
        $output .= '        </div>';
        $output .= '    </div>';
        $output .= '</div>';
    }

    wp_reset_postdata();
    return $output;
}
add_shortcode( 'patrocinadores', 'mp_mostrar_patrocinadores' );
