<?php
/**
 * Filters
 *
 * Filter callbacks for executing filters on the GraphQL Schema
 *
 * @package \WPGraphQL\Extensions\WooCommerce
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce;

use WPGraphQL\Extensions\WooCommerce\Data\Connection\WC_Posts_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Connection\WC_Terms_Connection_Resolver;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Extensions\WooCommerce\Data\Loader\Customer_Loader;
use WPGraphQL\Extensions\WooCommerce\Data\Loader\WC_Loader;

/**
 * Class Filters
 */
class Filters {
	/**
	 * Stores instance WC_Loader
	 *
	 * @var WC_Loader
	 */
	private static $wc_loader;

	/**
	 * Stores instance WC_Loader
	 *
	 * @var WC_Loader
	 */
	private static $customer_loader;

	/**
	 * Register filters
	 */
	public static function load() {
		add_filter(
			'register_post_type_args',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'register_post_type_args',
			),
			10,
			2
		);
		add_filter(
			'register_taxonomy_args',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'register_taxonomy_args',
			),
			10,
			2
		);

		add_filter(
			'graphql_data_loaders',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'graphql_data_loaders',
			),
			10,
			2
		);

		add_filter(
			'graphql_post_object_connection_query_args',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'graphql_post_object_connection_query_args',
			),
			10,
			5
		);

		add_filter(
			'graphql_term_object_connection_query_args',
			array(
				'\WPGraphQL\Extensions\WooCommerce\Filters',
				'graphql_term_object_connection_query_args',
			),
			10,
			5
		);
	}

	/**
	 * Initializes WC_Loader instance
	 *
	 * @param AppContext $context - AppContext.
	 *
	 * @return WC_Loader
	 */
	public static function wc_loader( $context ) {
		if ( empty( self::$wc_loader ) ) {
			self::$wc_loader = new WC_Loader( $context );
		}
		return self::$wc_loader;
	}

	/**
	 * Initializes Customer_Loader instance
	 *
	 * @param AppContext $context - AppContext.
	 *
	 * @return Customer_Loader
	 */
	public static function customer_loader( $context ) {
		if ( empty( self::$customer_loader ) ) {
			self::$customer_loader = new Customer_Loader( $context );
		}
		return self::$customer_loader;
	}

	/**
	 * Registers WooCommerce post-types to be used in GraphQL schema
	 *
	 * @param array  $args      - allowed post-types.
	 * @param string $post_type - name of post-type being checked.
	 *
	 * @return array
	 */
	public static function register_post_type_args( $args, $post_type ) {
		if ( 'product' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'product';
			$args['graphql_plural_name'] = 'products';
		}
		if ( 'product_variation' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productVariation';
			$args['graphql_plural_name'] = 'productVariations';
		}
		if ( 'shop_coupon' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'coupon';
			$args['graphql_plural_name'] = 'coupons';
		}
		if ( 'shop_order' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'order';
			$args['graphql_plural_name'] = 'orders';
		}
		if ( 'shop_order_refund' === $post_type ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'refund';
			$args['graphql_plural_name'] = 'refunds';
		}

		return $args;
	}

	/**
	 * Registers WooCommerce taxonomies to be used in GraphQL schema
	 *
	 * @param array  $args     - allowed post-types.
	 * @param string $taxonomy - name of taxonomy being checked.
	 *
	 * @return array
	 */
	public static function register_taxonomy_args( $args, $taxonomy ) {
		if ( 'product_type' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productType';
			$args['graphql_plural_name'] = 'productTypes';
		}

		if ( 'product_visibility' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'visibleProduct';
			$args['graphql_plural_name'] = 'visibleProducts';
		}

		if ( 'product_cat' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productCategory';
			$args['graphql_plural_name'] = 'productCategories';
		}

		if ( 'product_tag' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productTag';
			$args['graphql_plural_name'] = 'productTags';
		}

		if ( 'product_shipping_class' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'shippingClass';
			$args['graphql_plural_name'] = 'shippingClasses';
		}

		return $args;
	}

	/**
	 * Registers data-loaders to be used when resolving WooCommerce-related GraphQL types
	 *
	 * @param array      $loaders - assigned loaders.
	 * @param AppContext $context - AppContext instance.
	 *
	 * @return array
	 */
	public static function graphql_data_loaders( $loaders, $context ) {
		// WooCommerce customer loader.
		$customer_loader     = self::customer_loader( $context );
		$loaders['customer'] = &$customer_loader;

		// WooCommerce post-type loader.
		$wc_post_types = array(
			'shop_coupon',
			'product',
			'product_variation',
			'shop_order',
			'shop_order_refund',
		);
		$wc_loader     = self::wc_loader( $context );
		foreach ( $wc_post_types as $post_type ) {
			$loaders[ $post_type ] = &$wc_loader;
		}

		return $loaders;
	}

	/**
	 * Filter PostObjectConnectionResolver's query_args and adds args to used when querying WooCommerce post-types
	 *
	 * @param array       $query_args - WP_Query args.
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return mixed
	 */
	public static function graphql_post_object_connection_query_args( $query_args, $source, $args, $context, $info ) {
		return WC_Posts_Connection_Resolver::get_query_args( $query_args, $source, $args, $context, $info );
	}

	/**
	 * Filter TermObjectConnectionResolver's query_args and adds args to used when querying WooCommerce taxonomies
	 *
	 * @param array       $query_args - WP_Term_Query args.
	 * @param mixed       $source     - Connection parent resolver.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @return mixed
	 */
	public static function graphql_term_object_connection_query_args( $query_args, $source, $args, $context, $info ) {
		return WC_Terms_Connection_Resolver::get_query_args( $query_args, $source, $args, $context, $info );
	}
}
