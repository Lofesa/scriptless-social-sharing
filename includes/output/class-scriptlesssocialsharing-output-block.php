<?php

/**
 * Class ScriptlessSocialSharingOutputBlock
 */
class ScriptlessSocialSharingOutputBlock {

	/**
	 * The block name.
	 *
	 * @var string
	 */
	protected $name = 'scriptlesssocialsharing/buttons';

	/**
	 * @var string
	 */
	protected $block = 'scriptless-social-sharing-buttons';

	/**
	 * The plugin setting.
	 * @var array
	 */
	protected $setting;

	/**
	 * Register our block type.
	 */
	public function init() {
		$this->register_script_style();
		register_block_type(
			$this->name,
			array(
				'editor_script'   => $this->block . '-block',
				'editor_style'    => $this->block . '-block',
				'attributes'      => array_merge( $this->fields(), $this->networks() ),
				'render_callback' => array( $this, 'render' ),
			)
		);
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize' ) );
	}

	/**
	 * Render the widget in a container div.
	 *
	 * @param $atts
	 * @return string
	 */
	public function render( $atts ) {
		$classes = array(
			'wp-block-' . $this->block,
			$atts['className'],
		);
		if ( ! empty( $atts['blockAlignment'] ) ) {
			$classes[] = 'align' . $atts['blockAlignment'];
		}
		$atts['buttons'] = $this->parse_networks( $atts );
		$shortcode       = new ScriptlessSocialSharingOutputShortcode();
		$output          = '<div class="' . implode( ' ', $classes ) . '">';
		$output         .= $shortcode->shortcode( $atts );
		$output         .= '</div>';

		return $output;
	}

	/**
	 * Since buttons are chosen differently than our settings, we have to compare and parse.
	 *
	 * @param $atts
	 * @return string
	 */
	private function parse_networks( $atts ) {
		$buttons  = array();
		$networks = $this->networks();
		foreach ( $atts as $key => $value ) {
			if ( ! array_key_exists( $key, $networks ) ) {
				continue;
			}
			if ( $value ) {
				$buttons[] = $key;
			}
		}

		return implode( ',', $buttons );
	}

	/**
	 * Register the block script and style.
	 */
	public function register_script_style() {
		wp_register_style( $this->block . '-block', plugin_dir_url( dirname( __FILE__ ) ) . 'css/scriptlesssocialsharing-block.css', array(), SCRIPTLESSOCIALSHARING_VERSION, 'all' );
		$minify  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : ' . min';
		$version = $minify ? SCRIPTLESSOCIALSHARING_VERSION : SCRIPTLESSOCIALSHARING_VERSION . current_time( 'gmt' );
		wp_register_script(
			$this->block . '-block',
			plugin_dir_url( dirname( __FILE__ ) ) . "js/block{$minify}.js",
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			$version,
			false
		);
	}

	/**
	 * Localize.
	 */
	public function localize() {
		wp_localize_script( $this->block . '-block', 'ScriptlessBlock', $this->get_localization_data() );
	}

	/**
	 * Get the data for localizing everything.
	 * @return array
	 */
	protected function get_localization_data() {
		return array(
			'block'       => $this->name,
			'title'       => __( 'Scriptless Social Sharing', 'sixtenpress' ),
			'description' => __( 'Add sharing buttons anywhere', 'sixtenpress' ),
			'keywords'    => array(
				__( 'Social Share', 'sixtenpress' ),
				__( 'Sharing Buttons', 'sixtenpress' ),
			),
			'panels'      => array(
				'first' => array(
					'title'       => __( 'Block Settings', 'sixtenpress' ),
					'initialOpen' => true,
					'attributes'  => array_merge( $this->fields(), $this->networks() ),
				),
			),
			'icon'        => 'share',
			'category'    => 'widgets',
		);
	}

	/**
	 * Get the fields for the block.
	 * @return array
	 */
	private function fields() {
		return array(
			'blockAlignment' => array(
				'type'    => 'string',
				'default' => '',
			),
			'className'      => array(
				'type'    => 'string',
				'default' => '',
			),
			'heading'        => array(
				'type'    => 'string',
				'default' => $this->get_setting( 'heading' ),
				'label'   => __( 'Heading', 'scriptless-social-sharing' ),
			),
		);
	}

	/**
	 * Get the checkbox fields for the networks.
	 *
	 * @return array
	 */
	private function networks() {
		$setting  = $this->get_setting( 'buttons' );
		$networks = include plugin_dir_path( dirname( __FILE__ ) ) . 'settings/networks.php';
		$fields   = array();
		$i        = 0;
		foreach ( $networks as $network ) {
			$default                    = empty( $setting[ $network['name'] ] ) ? 0 : $setting[ $network['name'] ];
			$fields[ $network['name'] ] = array(
				'type'    => 'boolean',
				'default' => $default,
				'label'   => $network['label'],
				'method'  => 'checkbox',
			);
			if ( ! $i ) {
				$fields[ $network['name'] ]['heading'] = __( 'Buttons to Show', 'scriptless-social-sharing' );
			}
			$i++;
		}

		return $fields;
	}

	/**
	 * Get the plugin setting.
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 */
	protected function get_setting( $key = '' ) {
		if ( isset( $this->setting ) ) {
			return $key ? $this->setting[ $key ] : $this->setting;
		}
		$this->setting = scriptlesssocialsharing_get_setting();

		return $key ? $this->setting[ $key ] : $this->setting;
	}
}