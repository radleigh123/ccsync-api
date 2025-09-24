<?php

declare(strict_types=1);

return [
    /*
     * ------------------------------------------------------------------------
     * Default Firebase project
     * ------------------------------------------------------------------------
     */

    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
     * ------------------------------------------------------------------------
     * Firebase project configurations
     * ------------------------------------------------------------------------
     */

    'projects' => [
        'app' => [

            /*
             * ------------------------------------------------------------------------
             * Credentials / Service Account
             * ------------------------------------------------------------------------
             *
             * In order to access a Firebase project and its related services using a
             * server SDK, requests must be authenticated. For server-to-server
             * communication this is done with a Service Account.
             *
             * If you don't already have generated a Service Account, you can do so by
             * following the instructions from the official documentation pages at
             *
             * https://firebase.google.com/docs/admin/setup#initialize_the_sdk
             *
             * Once you have downloaded the Service Account JSON file, you can use it
             * to configure the package.
             *
             * If you don't provide credentials, the Firebase Admin SDK will try to
             * auto-discover them
             *
             * - by checking the environment variable FIREBASE_CREDENTIALS
             * - by checking the environment variable GOOGLE_APPLICATION_CREDENTIALS
             * - by trying to find Google's well known file
             * - by checking if the application is running on GCE/GCP
             *
             * If no credentials file can be found, an exception will be thrown the
             * first time you try to access a component of the Firebase Admin SDK.
             *
             */

            // 'credentials' => env('FIREBASE_CREDENTIALS', env('GOOGLE_APPLICATION_CREDENTIALS')),
            'credentials' => [
                "type" => "service_account",
                "project_id" => "ccsync-elphp-2025",
                "private_key_id" => "7af78b3c2cf1dfb5a3fd3493e9337626368b5025",
                "private_key" => "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQC1ecwMYKv8NsAu\nNZIBkFmpIFVRq5U046OcUWKR2fEj67ira910dcj+h+rtnmVLh1JLjx/uDCl/RdDi\nAMrvjuOJl6LqYZf5+REtYDEDF31hmEpXfFboLXD+q4lib/s9Gkyr5skiNG2MkwdM\n3u8KK3SDLy7DSG+jhipHZFt7Ziz5P5l7fzsUKBvUo/zzHxaWWdyI4fUbdM5Uj2VR\ntjR+9u4/pv4ovm1cIMebzzRez+rHDbyw1ESBkK/LerqEM5TXoNlwwGdKr8gyQW6y\nR0b/CnmecP4pAwA0lFyGBE2tiBz1MXpENeWgwD+DTmtiPPdY4eqMG607tR1MBPEt\nA/w2IDCRAgMBAAECggEAAhzvBbWKHceZFmtRw0+7YjHUmdJCDGmvzJYRrCnp5KBD\nQshgqFE8cU2YVu1z96dAS6MY56mVFxyzPq/B19G7BQMIEYC9kgUheyeGUpH6CAJL\nr/fsPsupUpHL6aKXqIxkmFDO2+WD4VyPO8jRvDV+0mM2K8MYElhG0uol0DVDm0r/\nqCr6xuTIkI1HCPrlGaYBB4p09u7SnGMl2+4K31GAEmKf8lCbrN4k6aId/FqaRnYB\nOXWDIWDgHvSsoN2x+1gAuuewAnTjM4yah65cEliMrX/6q5eOyHfazFz+LdAaF8ve\nyKK/krDWnHP72x56dsSQwkZPAeSC73LBJexWfyq3pQKBgQDt8EABDkaWGPXU6f4c\nkKpbaETK6LPnVp3WOCzUAV4cye2VtfQGRbwdhIcvMIXyEJ2ABrg9jblM+5f9KBf/\nqRcRjRcKeNKawwzbAnTvLiwMzAtIOIVoV7cNv+tmyElNPm8Ui3DEpT+8oOHA4Bpk\nSxLrAijxW8SNj/nuUxIL3SMMrQKBgQDDQFU/Q/PgQaVnm1aSHs/KajqIFgeIyoc2\nc2ULKEmnT7+6C+Q1x6TJeieFw34RSxMNAqcqd2A8CWfQReKoblANpPRJkKzmKngf\nLeppsfbEekfoWXm/xruAoafi9nv8AOm7zHDaYlQniS7AACLFLoIUiDhdCKqWZtnb\n2RVGbMQr9QKBgDU7WLZjwTr3XphBuU4et14315wlr4oEAM/aRX0wySNbscGasXtt\nwoZADhZqnqznNrVby4BJ4rjsWLaUb7oM1FJi5FK9cTCajpe41vxjsgsy4xtHOeF8\nLkHvO/UEvhF/9E3+XD5CUh4bSCZkfMMPYK4fD5Xf1/tryJifERyCXsMJAoGANAws\nLUcx9W/KZcn009K+1Vhn7erha0eBr7QnFUhSCfWqSC/vT56+gK69ZlzseDOpCmjQ\novNbheWD9PMMLpXpZRm5vPqB/IEJsFYPDMnR3CI0lO11FPgm920gUdIezth3dgZT\ndwOLJ1bcTXY7zpBNQKfnTnWG87zLCl3d2/4WdRkCgYAxVEV91fBxpok4Vx0folzR\noH/h3t62mOiQ8NtrG1F7TEeKL3Tsyy24M6aKALEcbqC1vGoZJVReWZ/rJFjh0blP\nBLIN6Hn5Run/d9YI3NEryBdaq8jBzkuVLIAO+u0nPHeGrIYJ2R5hUCGCnPyiW1O3\nZ5y9Pl/xdcT3hnVkxC2wrQ==\n-----END PRIVATE KEY-----\n",
                "client_email" => "firebase-adminsdk-fbsvc@ccsync-elphp-2025.iam.gserviceaccount.com",
                "client_id" => "107487882038029287540",
                "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
                "token_uri" => "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40ccsync-elphp-2025.iam.gserviceaccount.com",
                "universe_domain" => "googleapis.com"
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Auth Component
             * ------------------------------------------------------------------------
             */

            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firestore Component
             * ------------------------------------------------------------------------
             */

            'firestore' => [

                /*
                 * If you want to access a Firestore database other than the default database,
                 * enter its name here.
                 *
                 * By default, the Firestore client will connect to the `(default)` database.
                 *
                 * https://firebase.google.com/docs/firestore/manage-databases
                 */

                // 'database' => env('FIREBASE_FIRESTORE_DATABASE'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Realtime Database
             * ------------------------------------------------------------------------
             */

            'database' => [

                /*
                 * In most of the cases the project ID defined in the credentials file
                 * determines the URL of your project's Realtime Database. If the
                 * connection to the Realtime Database fails, you can override
                 * its URL with the value you see at
                 *
                 * https://console.firebase.google.com/u/1/project/_/database
                 *
                 * Please make sure that you use a full URL like, for example,
                 * https://my-project-id.firebaseio.com
                 */

                'url' => env('FIREBASE_DATABASE_URL'),

                /*
                 * As a best practice, a service should have access to only the resources it needs.
                 * To get more fine-grained control over the resources a Firebase app instance can access,
                 * use a unique identifier in your Security Rules to represent your service.
                 *
                 * https://firebase.google.com/docs/database/admin/start#authenticate-with-limited-privileges
                 */

                // 'auth_variable_override' => [
                //     'uid' => 'my-service-worker'
                // ],

            ],

            'dynamic_links' => [

                /*
                 * Dynamic links can be built with any URL prefix registered on
                 *
                 * https://console.firebase.google.com/u/1/project/_/durablelinks/links/
                 *
                 * You can define one of those domains as the default for new Dynamic
                 * Links created within your project.
                 *
                 * The value must be a valid domain, for example,
                 * https://example.page.link
                 */

                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Cloud Storage
             * ------------------------------------------------------------------------
             */

            'storage' => [

                /*
                 * Your project's default storage bucket usually uses the project ID
                 * as its name. If you have multiple storage buckets and want to
                 * use another one as the default for your application, you can
                 * override it here.
                 */

                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),

            ],

            /*
             * ------------------------------------------------------------------------
             * Caching
             * ------------------------------------------------------------------------
             *
             * The Firebase Admin SDK can cache some data returned from the Firebase
             * API, for example Google's public keys used to verify ID tokens.
             *
             */

            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
             * ------------------------------------------------------------------------
             * Logging
             * ------------------------------------------------------------------------
             *
             * Enable logging of HTTP interaction for insights and/or debugging.
             *
             * Log channels are defined in config/logging.php
             *
             * Successful HTTP messages are logged with the log level 'info'.
             * Failed HTTP messages are logged with the log level 'notice'.
             *
             * Note: Using the same channel for simple and debug logs will result in
             * two entries per request and response.
             */

            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            /*
             * ------------------------------------------------------------------------
             * HTTP Client Options
             * ------------------------------------------------------------------------
             *
             * Behavior of the HTTP Client performing the API requests
             */

            'http_client_options' => [

                /*
                 * Use a proxy that all API requests should be passed through.
                 * (default: none)
                 */

                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),

                /*
                 * Set the maximum amount of seconds (float) that can pass before
                 * a request is considered timed out
                 *
                 * The default time out can be reviewed at
                 * https://github.com/kreait/firebase-php/blob/6.x/src/Firebase/Http/HttpClientOptions.php
                 */

                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT'),

                'guzzle_middlewares' => [
                    // MyInvokableMiddleware::class,
                    // [MyMiddleware::class, 'static_method'],
                ],
            ],
        ],
    ],
];
