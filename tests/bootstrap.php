<?php

if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        private $code;
        private $message;

        public function __construct( $code = '', $message = '' ) {
            $this->code    = $code;
            $this->message = $message;
        }

        public function get_error_code() {
            return $this->code;
        }

        public function get_error_message() {
            return $this->message;
        }
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $hook_name, $value ) {
        return $value;
    }
}

if ( ! function_exists( 'get_user_locale' ) ) {
    function get_user_locale() {
        return $GLOBALS['wikipedia_app_test_user_locale'] ?? 'en_US';
    }
}

if ( ! function_exists( 'get_locale' ) ) {
    function get_locale() {
        return 'en_US';
    }
}

if ( ! function_exists( 'home_url' ) ) {
    function home_url( $path = '' ) {
        $home = $GLOBALS['wikipedia_app_test_home_url'] ?? 'https://example.test';
        return rtrim( $home, '/' ) . ( '/' === substr( $path, 0, 1 ) ? $path : '/' . $path );
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $value ) {
        return trim( strip_tags( (string) $value ) );
    }
}

if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data, $options = 0, $depth = 512 ) {
        return json_encode( $data, $options, $depth );
    }
}

if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash( $value ) {
        return is_string( $value ) ? stripslashes( $value ) : $value;
    }
}

if ( ! function_exists( 'wp_remote_retrieve_header' ) ) {
    function wp_remote_retrieve_header( $response, $header ) {
        $header = strtolower( (string) $header );
        foreach ( $response['headers'] ?? [] as $name => $value ) {
            if ( strtolower( (string) $name ) === $header ) {
                return $value;
            }
        }

        return '';
    }
}

if ( ! function_exists( 'add_query_arg' ) ) {
    function add_query_arg( $args, $url = '' ) {
        if ( ! is_array( $args ) ) {
            return $url;
        }

        $fragment = '';
        $hash_pos = strpos( $url, '#' );
        if ( false !== $hash_pos ) {
            $fragment = substr( $url, $hash_pos );
            $url      = substr( $url, 0, $hash_pos );
        }

        $parts = parse_url( $url );
        $query = [];
        if ( isset( $parts['query'] ) ) {
            parse_str( $parts['query'], $query );
        }

        foreach ( $args as $key => $value ) {
            if ( false === $value || null === $value ) {
                unset( $query[ $key ] );
            } else {
                $query[ $key ] = $value;
            }
        }

        $base = $url;
        if ( isset( $parts['query'] ) ) {
            $base = substr( $url, 0, -strlen( '?' . $parts['query'] ) );
        }

        return $base . ( $query ? '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 ) : '' ) . $fragment;
    }
}

if ( ! function_exists( 'sanitize_title' ) ) {
    function sanitize_title( $title ) {
        $title = strtolower( (string) $title );
        $title = preg_replace( '/[^a-z0-9]+/', '-', $title );
        return trim( $title, '-' );
    }
}

if ( ! function_exists( 'absint' ) ) {
    function absint( $value ) {
        return abs( (int) $value );
    }
}

if ( ! function_exists( 'esc_url_raw' ) ) {
    function esc_url_raw( $url ) {
        return (string) $url;
    }
}

if ( ! function_exists( 'wp_parse_url' ) ) {
    function wp_parse_url( $url ) {
        return parse_url( $url );
    }
}

if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return $thing instanceof WP_Error;
    }
}

require dirname( __DIR__ ) . '/vendor/autoload.php';
