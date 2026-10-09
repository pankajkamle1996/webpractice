<?php
/**
 * Plugin Name: WP AI Assistant
 * Description: A custom WordPress AI assistant.
 * Version: 1.0.0
 * Author: Pankaj
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wp_ai_assistant_enqueue_assets() {

    wp_enqueue_style(
        'wp-ai-assistant-style',
        plugin_dir_url( __FILE__ ) . 'assets/css/ai-assistant.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'wp-ai-assistant-script',
        plugin_dir_url( __FILE__ ) . 'assets/js/ai-assistant.js',
        array(),
        '1.0.0',
        true
    );
}

add_action(
    'wp_enqueue_scripts',
    'wp_ai_assistant_enqueue_assets'
);
function wp_ai_assistant_shortcode() {

    $answer = '';
    $error  = '';

    if (
        isset( $_POST['wp_ai_question'] ) &&
        isset( $_POST['wp_ai_nonce'] ) &&
        wp_verify_nonce(
            sanitize_text_field(
                wp_unslash( $_POST['wp_ai_nonce'] )
            ),
            'wp_ai_ask'
        )
    ) {
        $question = sanitize_textarea_field(
            wp_unslash( $_POST['wp_ai_question'] )
        );

        if ( '' === trim( $question ) ) {
            $error = 'Please enter a question.';
        } elseif ( strlen( $question ) > 2000 ) {
            $error = 'Your question is too long. Please use fewer than 2000 characters.';
        } else {
            $result = wp_ai_assistant_get_response(
                $question
            );

            if ( is_wp_error( $result ) ) {
                $error = $result->get_error_message();
            } else {
                $answer = $result;
            }
        }
    }

    ob_start();
    ?>
    <div class="wp-ai-assistant">
        <h3>AI Assistant</h3>

        <?php if ( $error ) : ?>
            <div class="ai-error" role="alert">
                <?php echo esc_html( $error ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $answer ) : ?>
            <div class="ai-answer" role="status">
                <strong>AI Response:</strong>
                <p><?php echo esc_html( $answer ); ?></p>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php
            wp_nonce_field(
                'wp_ai_ask',
                'wp_ai_nonce'
            );
            ?>

            <label for="wp-ai-question">
                Ask your question
            </label>

            <textarea
                id="wp-ai-question"
                name="wp_ai_question"
                rows="4"
                maxlength="2000"
                required
            ><?php
                if ( isset( $question ) ) {
                    echo esc_textarea( $question );
                }
            ?></textarea>

            <button type="submit">Ask AI</button>
        </form>
    </div>
    <?php

    return ob_get_clean();
}

add_shortcode(
    'wp_ai_assistant',
    'wp_ai_assistant_shortcode'
);


function wp_ai_assistant_get_response( $question ) {

    $api_key = defined( 'OPENAI_API_KEY' )
        ? OPENAI_API_KEY
        : '';

    if ( empty( $api_key ) ) {
        return new WP_Error(
            'missing_api_key',
            'AI API key is not configured.'
        );
    }

    $response = wp_remote_post(
        'https://api.openai.com/v1/responses',
        array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode(
                array(
                    'model' => 'gpt-4.1-mini',
                    'input' => $question,
                )
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        return new WP_Error(
            'api_request_failed',
            'Unable to connect to the AI service.'
        );
    }

    $status_code = wp_remote_retrieve_response_code(
        $response
    );

    $body = json_decode(
        wp_remote_retrieve_body( $response ),
        true
    );


    if ( $status_code < 200 || $status_code >= 300 ) {

        $api_message = $body['error']['message']
            ?? 'Unknown API error';

        error_log(
            'OpenAI API HTTP ' . $status_code
            . ': ' . sanitize_text_field( $api_message )
        );

        return new WP_Error(
            'api_error',
            'AI API request failed. HTTP status: '
            . $status_code
        );
    }

    if ( ! is_array( $body ) ) {
        return new WP_Error(
            'invalid_response',
            'Invalid response received from AI API.'
        );
    }

    if ( ! empty( $body['error'] ) ) {
        return new WP_Error(
            'api_error',
            'The AI service could not process the request.'
        );
    }

    $answer = '';

    foreach ( $body['output'] ?? array() as $item ) {
        if ( ( $item['type'] ?? '' ) !== 'message' ) {
            continue;
        }

        foreach ( $item['content'] ?? array() as $content ) {
            if ( ( $content['type'] ?? '' ) === 'output_text' ) {
                $answer .= $content['text'] ?? '';
            }
        }
    }

    if ( '' === trim( $answer ) ) {
        return new WP_Error(
            'empty_ai_response',
            'No text response was returned.'
        );
    }

    return $answer;
}