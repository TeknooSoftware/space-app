<?php

// This file is auto-generated and is for apps only. Bundles SHOULD NOT rely on its content.

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Config\Loader\ParamConfigurator as Param;

/**
 * This class provides array-shapes for configuring the services and bundles of an application.
 *
 * Services declared with the config() method below are autowired and autoconfigured by default.
 *
 * This is for apps only. Bundles SHOULD NOT use it.
 *
 * Example:
 *
 *     ```php
 *     // config/services.php
 *     namespace Symfony\Component\DependencyInjection\Loader\Configurator;
 *
 *     return App::config([
 *         'services' => [
 *             'App\\' => [
 *                 'resource' => '../src/',
 *             ],
 *         ],
 *     ]);
 *     ```
 *
 * @psalm-type ImportsConfig = list<string|array{
 *     resource: string,
 *     type?: string|null,
 *     ignore_errors?: bool,
 * }>
 * @psalm-type ParametersConfig = array<string, scalar|\UnitEnum|array<scalar|\UnitEnum|array<mixed>|\Symfony\Component\Config\Loader\ParamConfigurator|null>|\Symfony\Component\Config\Loader\ParamConfigurator|null>
 * @psalm-type ArgumentsType = list<mixed>|array<string, mixed>
 * @psalm-type CallType = array<string, ArgumentsType>|array{0:string, 1?:ArgumentsType, 2?:bool}|array{method:string, arguments?:ArgumentsType, returns_clone?:bool}
 * @psalm-type TagsType = list<string|array<string, array<string, mixed>>> // arrays inside the list must have only one element, with the tag name as the key
 * @psalm-type CallbackType = string|array{0:string|ReferenceConfigurator,1:string}|\Closure|ReferenceConfigurator|ExpressionConfigurator
 * @psalm-type DeprecationType = array{package: string, version: string, message?: string}
 * @psalm-type DefaultsType = array{
 *     public?: bool,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 * }
 * @psalm-type InstanceofType = array{
 *     shared?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     properties?: array<string, mixed>,
 *     configurator?: CallbackType,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 * }
 * @psalm-type DefinitionType = array{
 *     class?: string,
 *     file?: string,
 *     parent?: string,
 *     shared?: bool,
 *     synthetic?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     abstract?: bool,
 *     deprecated?: DeprecationType,
 *     factory?: CallbackType,
 *     configurator?: CallbackType,
 *     arguments?: ArgumentsType,
 *     properties?: array<string, mixed>,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     decorates?: string,
 *     decoration_inner_name?: string,
 *     decoration_priority?: int,
 *     decoration_on_invalid?: 'exception'|'ignore'|null,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 *     from_callable?: CallbackType,
 * }
 * @psalm-type AliasType = string|array{
 *     alias: string,
 *     public?: bool,
 *     deprecated?: DeprecationType,
 * }
 * @psalm-type PrototypeType = array{
 *     resource: string,
 *     namespace?: string,
 *     exclude?: string|list<string>,
 *     parent?: string,
 *     shared?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     abstract?: bool,
 *     deprecated?: DeprecationType,
 *     factory?: CallbackType,
 *     arguments?: ArgumentsType,
 *     properties?: array<string, mixed>,
 *     configurator?: CallbackType,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 * }
 * @psalm-type StackType = array{
 *     stack: list<DefinitionType|AliasType|PrototypeType|array<class-string, ArgumentsType|null>>,
 *     public?: bool,
 *     deprecated?: DeprecationType,
 * }
 * @psalm-type ServicesConfig = array{
 *     _defaults?: DefaultsType,
 *     _instanceof?: InstanceofType,
 *     ...<string, DefinitionType|AliasType|PrototypeType|StackType|ArgumentsType|null>
 * }
 * @psalm-type ExtensionType = array<string, mixed>
 * @psalm-type DoctrineMongodbConfig = array{
 *     document_managers?: array<string, array{ // Default: []
 *         connection?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         database?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         logging?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "%kernel.debug%"
 *         profiler?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "%kernel.debug%"
 *             pretty?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "%kernel.debug%"
 *         },
 *         default_document_repository_class?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Doctrine\\ODM\\MongoDB\\Repository\\DocumentRepository"
 *         default_gridfs_repository_class?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Doctrine\\ODM\\MongoDB\\Repository\\DefaultGridFSRepository"
 *         repository_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "doctrine_mongodb.odm.container_repository_factory"
 *         persistent_collection_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         auto_mapping?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         filters?: array<string, string|array{ // Default: []
 *             class: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             parameters?: array<string, mixed>,
 *         }>,
 *         metadata_cache_driver?: string|array{
 *             type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "array"
 *             class?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             port?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             instance_class?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             namespace?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         },
 *         use_transactional_flush?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         mappings?: array<string, bool|string|array{ // Default: []
 *             mapping?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *             type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             prefix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             alias?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             is_bundle?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         }>,
 *     }>,
 *     connections?: array<string, array{ // Default: []
 *         server?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         options?: array{
 *             authMechanism?: "SCRAM-SHA-1"|"SCRAM-SHA-256"|"MONGODB-CR"|"MONGODB-X509"|"PLAIN"|"GSSAPI"|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             connectTimeoutMS?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             db?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             authSource?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             journal?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             readPreference?: "primary"|"primaryPreferred"|"secondary"|"secondaryPreferred"|"nearest"|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             readPreferenceTags?: list<array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>>,
 *             replicaSet?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             socketTimeoutMS?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             ssl?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             tls?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             tlsAllowInvalidCertificates?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             tlsAllowInvalidHostnames?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             tlsCAFile?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             tlsCertificateKeyFile?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             tlsCertificateKeyFilePassword?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             tlsDisableCertificateRevocationCheck?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             tlsDisableOCSPEndpointCheck?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             tlsInsecure?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             username?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             retryReads?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             retryWrites?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             w?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             wTimeoutMS?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         },
 *         driver_options?: array{
 *             context?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Deprecated: The "context" driver option is deprecated and will be removed in 3.0. This option is ignored by the MongoDB driver version 2. // Default: null
 *         },
 *         autoEncryption?: array{
 *             bypassAutoEncryption?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             keyVaultClient?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             keyVaultNamespace?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             masterKey?: list<mixed>,
 *             kmsProvider: array{
 *                 type: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 accessKeyId?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 secretAccessKey?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 sessionToken?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 tenantId?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 clientId?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 clientSecret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 keyVaultEndpoint?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 identityPlatformEndpoint?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 keyName?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 keyVersion?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 email?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 privateKey?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 endpoint?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 projectId?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 location?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 keyRing?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             },
 *             schemaMap?: list<mixed>,
 *             encryptedFieldsMap?: array<string, array{ // Default: []
 *                 fields?: list<array{ // Default: []
 *                     path: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                     bsonType: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                     keyId: mixed,
 *                     queries?: array{
 *                         queryType: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                         min?: mixed,
 *                         max?: mixed,
 *                         sparsity?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                         precision?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                         trimFactor?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                         contention?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                     },
 *                 }>,
 *             }>,
 *             extraOptions?: array{
 *                 mongocryptdURI?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 mongocryptdBypassSpawn?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                 mongocryptdSpawnPath?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 mongocryptdSpawnArgs?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                 cryptSharedLibPath?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 cryptSharedLibRequired?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             },
 *             bypassQueryAnalysis?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             tlsOptions?: array{
 *                 tlsCAFile?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 tlsCertificateKeyFile?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 tlsCertificateKeyFilePassword?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 tlsDisableOCSPEndpointCheck?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             },
 *         },
 *     }>,
 *     resolve_target_documents?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     types?: array<string, string|array{ // Default: []
 *         class: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     }>,
 *     proxy_namespace?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "MongoDBODMProxies"
 *     proxy_dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.cache_dir%/doctrine/odm/mongodb/Proxies"
 *     enable_native_lazy_objects?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: The "enable_native_lazy_objects" option is deprecated and will be removed in 6.0. Native Lazy Objects are enable by default when using PHP 8.4+ and doctrine/mongodb-odm 2.14+. // Requires PHP 8.4+ and doctrine/mongodb-odm 2.14+ // Default: true
 *     enable_lazy_ghost_objects?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: The "enable_lazy_ghost_objects" option is deprecated and will be removed in 6.0. Native Lazy Objects are enable by default when using PHP 8.4+ and doctrine/mongodb-odm 2.14+. // Requires doctrine/mongodb-odm 2.12+ // Default: true
 *     auto_generate_proxy_classes?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 3
 *     hydrator_namespace?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Hydrators"
 *     hydrator_dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.cache_dir%/doctrine/odm/mongodb/Hydrators"
 *     auto_generate_hydrator_classes?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *     persistent_collection_namespace?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "PersistentCollections"
 *     persistent_collection_dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.cache_dir%/doctrine/odm/mongodb/PersistentCollections"
 *     auto_generate_persistent_collection_classes?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *     default_document_manager?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     default_connection?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     default_database?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "default"
 *     default_commit_options?: array{
 *         j?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         timeout?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         w?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         wtimeout?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *     },
 *     controller_resolver?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         auto_mapping?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Set to false to disable using route placeholders as lookup criteria when the object id doesn't match the argument name // Default: true
 *     },
 * }
 * @psalm-type DiBridgeConfig = array{
 *     compilation_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     enable_cache?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     definitions?: list<string|array{ // Default: []
 *         priority?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         file?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     }>,
 *     import?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     extensions?: list<string|array{ // Default: []
 *         priority?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     }>,
 * }
 * @psalm-type EastFoundationConfig = array<mixed>
 * @psalm-type TeknooEastCommonConfig = array<mixed>
 * @psalm-type TeknooEastPaasConfig = array<mixed>
 * @psalm-type FrameworkConfig = array{
 *     secret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     http_method_override?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Set true to enable support for the '_method' request parameter to determine the intended HTTP method on POST requests. // Default: false
 *     allowed_http_method_override?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>|null,
 *     trust_x_sendfile_type_header?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Set true to enable support for xsendfile in binary file responses. // Default: "%env(bool:default::SYMFONY_TRUST_X_SENDFILE_TYPE_HEADER)%"
 *     ide?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%env(default::SYMFONY_IDE)%"
 *     test?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *     default_locale?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "en"
 *     set_locale_from_accept_language?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether to use the Accept-Language HTTP header to set the Request locale (only when the "_locale" request attribute is not passed). // Default: false
 *     set_content_language_from_locale?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether to set the Content-Language HTTP header on the Response using the Request locale. // Default: false
 *     enabled_locales?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     trusted_hosts?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     trusted_proxies?: mixed, // Default: ["%env(default::SYMFONY_TRUSTED_PROXIES)%"]
 *     trusted_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     error_controller?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "error_controller"
 *     handle_all_throwables?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // HttpKernel will handle all kinds of \Throwable. // Default: true
 *     csrf_protection?: bool|array{
 *         enabled?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         stateless_token_ids?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         check_header?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Whether to check the CSRF token in a header in addition to a cookie when using stateless protection. // Default: false
 *         cookie_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of the cookie to use when using stateless protection. // Default: "csrf-token"
 *     },
 *     form?: bool|array{ // Form configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         csrf_protection?: bool|array{
 *             enabled?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             token_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             field_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_token"
 *             field_attr?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *     },
 *     http_cache?: bool|array{ // HTTP cache configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         debug?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "%kernel.debug%"
 *         trace_level?: "none"|"short"|"full"|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         trace_header?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         default_ttl?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         private_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         skip_response_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_reload?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         allow_revalidate?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         stale_while_revalidate?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         stale_if_error?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         terminate_on_cache_hit?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *     },
 *     esi?: bool|array{ // ESI configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     ssi?: bool|array{ // SSI configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     fragments?: bool|array{ // Fragments configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         hinclude_default_template?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/_fragment"
 *     },
 *     profiler?: bool|array{ // Profiler configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         collect?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         collect_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of the parameter to use to enable or disable collection on a per request basis. // Default: null
 *         only_exceptions?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         only_main_requests?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         dsn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "file:%kernel.cache_dir%/profiler"
 *         collect_serializer_data?: true|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     workflows?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         workflows?: array<string, array{ // Default: []
 *             audit_trail?: bool|array{
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             },
 *             type?: "workflow"|"state_machine"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "state_machine"
 *             marking_store?: array{
 *                 type?: "method"|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                 property?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             },
 *             supports?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             definition_validators?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             support_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             initial_marking?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             events_to_dispatch?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>|null,
 *             places?: list<array{ // Default: []
 *                 name: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 metadata?: list<mixed>,
 *             }>,
 *             transitions: list<array{ // Default: []
 *                 name: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                 guard?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // An expression to block the transition.
 *                 from?: list<array{ // Default: []
 *                     place: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                     weight?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 1
 *                 }>,
 *                 to?: list<array{ // Default: []
 *                     place: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                     weight?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 1
 *                 }>,
 *                 weight?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 1
 *                 metadata?: list<mixed>,
 *             }>,
 *             metadata?: list<mixed>,
 *         }>,
 *     },
 *     router?: bool|array{ // Router configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         resource: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         default_uri?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The default URI used to generate URLs in a non-HTTP context. // Default: null
 *         http_port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 80
 *         https_port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 443
 *         strict_requirements?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // set to true to throw an exception when a parameter does not match the requirements set to false to disable exceptions when a parameter does not match the requirements (and return null instead) set to null to disable parameter checks against requirements 'true' is the preferred configuration in development mode, while 'false' or 'null' might be preferred in production // Default: true
 *         utf8?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     session?: bool|array{ // Session configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         storage_factory_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "session.storage.factory.native"
 *         handler_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to using the native session handler, or to the native *file* session handler if "save_path" is not null.
 *         name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         cookie_lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         cookie_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         cookie_domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         cookie_secure?: true|false|"auto"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "auto"
 *         cookie_httponly?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         cookie_samesite?: null|"lax"|"strict"|"none"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "lax"
 *         use_cookies?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         gc_divisor?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         gc_probability?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         gc_maxlifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         save_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to "%kernel.cache_dir%/sessions" if the "handler_id" option is not null.
 *         metadata_update_threshold?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Seconds to wait between 2 session metadata updates. // Default: 0
 *     },
 *     request?: bool|array{ // Request configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         formats?: array<string, string|list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>>,
 *     },
 *     assets?: bool|array{ // Assets configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         strict_mode?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Throw an exception if an entry is missing from the manifest.json. // Default: false
 *         version_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         version?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         version_format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%%s?%%s"
 *         json_manifest_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         base_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         base_urls?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         packages?: array<string, array{ // Default: []
 *             strict_mode?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Throw an exception if an entry is missing from the manifest.json. // Default: false
 *             version_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             version?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             version_format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             json_manifest_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             base_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             base_urls?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *     },
 *     asset_mapper?: bool|array{ // Asset Mapper configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         paths?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         excluded_patterns?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         exclude_dotfiles?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // If true, any files starting with "." will be excluded from the asset mapper. // Default: true
 *         server?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // If true, a "dev server" will return the assets from the public directory (true in "debug" mode only by default). // Default: true
 *         public_prefix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The public path where the assets will be written to (and served from when "server" is true). // Default: "/assets/"
 *         missing_import_mode?: "strict"|"warn"|"ignore"|\Symfony\Component\Config\Loader\ParamConfigurator, // Behavior if an asset cannot be found when imported from JavaScript or CSS files - e.g. "import './non-existent.js'". "strict" means an exception is thrown, "warn" means a warning is logged, "ignore" means the import is left as-is. // Default: "warn"
 *         extensions?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         importmap_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path of the importmap.php file. // Default: "%kernel.project_dir%/importmap.php"
 *         importmap_polyfill?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The importmap name that will be used to load the polyfill. Set to false to disable. // Default: "es-module-shims"
 *         importmap_script_attributes?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         vendor_dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The directory to store JavaScript vendors. // Default: "%kernel.project_dir%/assets/vendor"
 *         precompress?: bool|array{ // Precompress assets with Brotli, Zstandard and gzip.
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             formats?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             extensions?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *     },
 *     translator?: bool|array{ // Translator configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         fallbacks?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         logging?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         formatter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "translator.formatter.default"
 *         cache_dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.cache_dir%/translations"
 *         default_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The default path used to load translations. // Default: "%kernel.project_dir%/translations"
 *         paths?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         pseudo_localization?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             accents?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             expansion_factor?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 1.0
 *             brackets?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             parse_html?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             localizable_html_attributes?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         providers?: array<string, array{ // Default: []
 *             dsn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             domains?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             locales?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *         globals?: array<string, string|array{ // Default: []
 *             value?: mixed,
 *             message?: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             parameters?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             domain?: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         }>,
 *     },
 *     validation?: bool|array{ // Validation configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         enable_attributes?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         static_method?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         translation_domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "validators"
 *         email_validation_mode?: "html5"|"html5-allow-no-tld"|"strict"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "html5"
 *         mapping?: array{
 *             paths?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         not_compromised_password?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // When disabled, compromised passwords will be accepted as valid. // Default: true
 *             endpoint?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // API endpoint for the NotCompromisedPassword Validator. // Default: null
 *         },
 *         disable_translation?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         auto_mapping?: array<string, array{ // Default: []
 *             services?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *     },
 *     serializer?: bool|array{ // Serializer configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         enable_attributes?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         name_converter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         circular_reference_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         max_depth_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         mapping?: array{
 *             paths?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         default_context?: list<mixed>,
 *         named_serializers?: array<string, array{ // Default: []
 *             name_converter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             default_context?: list<mixed>,
 *             include_built_in_normalizers?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether to include the built-in normalizers // Default: true
 *             include_built_in_encoders?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether to include the built-in encoders // Default: true
 *         }>,
 *     },
 *     property_access?: bool|array{ // Property access configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         magic_call?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         magic_get?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         magic_set?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         throw_exception_on_invalid_index?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         throw_exception_on_invalid_property_path?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     type_info?: bool|array{ // Type info configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         aliases?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     },
 *     property_info?: bool|array{ // Property info configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         with_constructor_extractor?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Registers the constructor extractor. // Default: true
 *     },
 *     cache?: array{ // Cache configuration
 *         prefix_seed?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Used to namespace cache keys when using several apps with the same shared backend. // Default: "_%kernel.project_dir%.%kernel.container_class%"
 *         app?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // App related cache pools configuration. // Default: "cache.adapter.filesystem"
 *         system?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // System related cache pools configuration. // Default: "cache.adapter.system"
 *         directory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.share_dir%/pools/app"
 *         default_psr6_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         default_redis_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "redis://localhost"
 *         default_valkey_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "valkey://localhost"
 *         default_memcached_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "memcached://localhost"
 *         default_doctrine_dbal_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "database_connection"
 *         default_pdo_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         pools?: array<string, array{ // Default: []
 *             adapters?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             tags?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             public?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             default_lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default lifetime of the pool.
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Overwrite the setting from the default provider for this adapter.
 *             early_expiration_message_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             clearer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         }>,
 *     },
 *     php_errors?: array{ // PHP errors handling configuration
 *         log?: mixed, // Use the application logger instead of the PHP logger for logging PHP errors. // Default: true
 *         throw?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Throw PHP errors as \ErrorException instances. // Default: true
 *     },
 *     exceptions?: array<string, array{ // Default: []
 *         log_level?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The level of log message. Null to let Symfony decide. // Default: null
 *         status_code?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The status code of the response. Null or 0 to let Symfony decide. // Default: null
 *         log_channel?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The channel of log message. Null to let Symfony decide. // Default: null
 *     }>,
 *     web_link?: bool|array{ // Web links configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     lock?: bool|string|array{ // Lock configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         resources?: array<string, string|list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>>,
 *     },
 *     semaphore?: bool|string|array{ // Semaphore configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         resources?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     },
 *     messenger?: bool|array{ // Messenger configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         routing?: array<string, array{ // Default: []
 *             senders?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *         serializer?: array{
 *             default_serializer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Service id to use as the default serializer for the transports. // Default: "messenger.transport.native_php_serializer"
 *             symfony_serializer?: array{
 *                 format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Serialization format for the messenger.transport.symfony_serializer service (which is not the serializer used by default). // Default: "json"
 *                 context?: array<string, mixed>,
 *             },
 *         },
 *         transports?: array<string, string|array{ // Default: []
 *             dsn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             serializer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Service id of a custom serializer to use. // Default: null
 *             options?: list<mixed>,
 *             failure_transport?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Transport name to send failed messages to (after all retries have failed). // Default: null
 *             retry_strategy?: string|array{
 *                 service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Service id to override the retry strategy entirely. // Default: null
 *                 max_retries?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 3
 *                 delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *                 multiplier?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // If greater than 1, delay will grow exponentially for each retry: this delay = (delay * (multiple ^ retries)). // Default: 2
 *                 max_delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *                 jitter?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // Randomness to apply to the delay (between 0 and 1). // Default: 0.1
 *             },
 *             rate_limiter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Rate limiter name to use when processing messages. // Default: null
 *         }>,
 *         failure_transport?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Transport name to send failed messages to (after all retries have failed). // Default: null
 *         stop_worker_on_signals?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         default_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         buses?: array<string, array{ // Default: {"messenger.bus.default":{"default_middleware":{"enabled":true,"allow_no_handlers":false,"allow_no_senders":true},"middleware":[]}}
 *             default_middleware?: bool|string|array{
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *                 allow_no_handlers?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 allow_no_senders?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             },
 *             middleware?: list<string|array{ // Default: []
 *                 id: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 arguments?: list<mixed>,
 *             }>,
 *         }>,
 *     },
 *     scheduler?: bool|array{ // Scheduler configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     disallow_search_engine_index?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Enabled by default when debug is enabled. // Default: true
 *     http_client?: bool|array{ // HTTP Client configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         max_host_connections?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum number of connections to a single host.
 *         default_options?: array{
 *             headers?: array<string, mixed>,
 *             vars?: array<string, mixed>,
 *             max_redirects?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum number of redirects to follow.
 *             http_version?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The default HTTP version, typically 1.1 or 2.0, leave to null for the best version.
 *             resolve?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             proxy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The URL of the proxy to pass requests through or null for automatic detection.
 *             no_proxy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A comma separated list of hosts that do not require a proxy to be reached.
 *             timeout?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // The idle timeout, defaults to the "default_socket_timeout" ini parameter.
 *             max_duration?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum execution time for the request+response as a whole.
 *             bindto?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A network interface name, IP address, a host name or a UNIX socket to bind to.
 *             verify_peer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates if the peer should be verified in a TLS context.
 *             verify_host?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates if the host should exist as a certificate common name.
 *             cafile?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A certificate authority file.
 *             capath?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A directory that contains multiple certificate authority files.
 *             local_cert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A PEM formatted certificate file.
 *             local_pk?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A private key file.
 *             passphrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The passphrase used to encrypt the "local_pk" file.
 *             ciphers?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A list of TLS ciphers separated by colons, commas or spaces (e.g. "RC3-SHA:TLS13-AES-128-GCM-SHA256"...)
 *             peer_fingerprint?: array{ // Associative array: hashing algorithm => hash(es).
 *                 sha1?: mixed,
 *                 pin-sha256?: mixed,
 *                 md5?: mixed,
 *             },
 *             crypto_method?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The minimum version of TLS to accept; must be one of STREAM_CRYPTO_METHOD_TLSv*_CLIENT constants.
 *             extra?: array<string, mixed>,
 *             rate_limiter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Rate limiter name to use for throttling requests. // Default: null
 *             caching?: bool|array{ // Caching configuration.
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 cache_pool?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // The taggable cache pool to use for storing the responses. // Default: "cache.http_client"
 *                 shared?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates whether the cache is shared (public) or private. // Default: true
 *                 max_ttl?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum TTL (in seconds) allowed for cached responses. Null means no cap. // Default: null
 *             },
 *             retry_failed?: bool|array{
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 retry_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // service id to override the retry strategy. // Default: null
 *                 http_codes?: array<string, array{ // Default: []
 *                     code?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                     methods?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *                 }>,
 *                 max_retries?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 3
 *                 delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *                 multiplier?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // If greater than 1, delay will grow exponentially for each retry: delay * (multiple ^ retries). // Default: 2
 *                 max_delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *                 jitter?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // Randomness in percent (between 0 and 1) to apply to the delay. // Default: 0.1
 *             },
 *         },
 *         mock_response_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The id of the service that should generate mock responses. It should be either an invokable or an iterable.
 *         scoped_clients?: array<string, string|array{ // Default: []
 *             scope?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The regular expression that the request URL must match before adding the other options. When none is provided, the base URI is used instead.
 *             base_uri?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The URI to resolve relative URLs, following rules in RFC 3985, section 2.
 *             auth_basic?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // An HTTP Basic authentication "username:password".
 *             auth_bearer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A token enabling HTTP Bearer authorization.
 *             auth_ntlm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A "username:password" pair to use Microsoft NTLM authentication (requires the cURL extension).
 *             query?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             headers?: array<string, mixed>,
 *             max_redirects?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum number of redirects to follow.
 *             http_version?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The default HTTP version, typically 1.1 or 2.0, leave to null for the best version.
 *             resolve?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             proxy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The URL of the proxy to pass requests through or null for automatic detection.
 *             no_proxy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A comma separated list of hosts that do not require a proxy to be reached.
 *             timeout?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // The idle timeout, defaults to the "default_socket_timeout" ini parameter.
 *             max_duration?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum execution time for the request+response as a whole.
 *             bindto?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A network interface name, IP address, a host name or a UNIX socket to bind to.
 *             verify_peer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates if the peer should be verified in a TLS context.
 *             verify_host?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates if the host should exist as a certificate common name.
 *             cafile?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A certificate authority file.
 *             capath?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A directory that contains multiple certificate authority files.
 *             local_cert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A PEM formatted certificate file.
 *             local_pk?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A private key file.
 *             passphrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The passphrase used to encrypt the "local_pk" file.
 *             ciphers?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A list of TLS ciphers separated by colons, commas or spaces (e.g. "RC3-SHA:TLS13-AES-128-GCM-SHA256"...).
 *             peer_fingerprint?: array{ // Associative array: hashing algorithm => hash(es).
 *                 sha1?: mixed,
 *                 pin-sha256?: mixed,
 *                 md5?: mixed,
 *             },
 *             crypto_method?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The minimum version of TLS to accept; must be one of STREAM_CRYPTO_METHOD_TLSv*_CLIENT constants.
 *             extra?: array<string, mixed>,
 *             rate_limiter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Rate limiter name to use for throttling requests. // Default: null
 *             caching?: bool|array{ // Caching configuration.
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 cache_pool?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // The taggable cache pool to use for storing the responses. // Default: "cache.http_client"
 *                 shared?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates whether the cache is shared (public) or private. // Default: true
 *                 max_ttl?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum TTL (in seconds) allowed for cached responses. Null means no cap. // Default: null
 *             },
 *             retry_failed?: bool|array{
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 retry_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // service id to override the retry strategy. // Default: null
 *                 http_codes?: array<string, array{ // Default: []
 *                     code?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                     methods?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *                 }>,
 *                 max_retries?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 3
 *                 delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *                 multiplier?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // If greater than 1, delay will grow exponentially for each retry: delay * (multiple ^ retries). // Default: 2
 *                 max_delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *                 jitter?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // Randomness in percent (between 0 and 1) to apply to the delay. // Default: 0.1
 *             },
 *         }>,
 *     },
 *     mailer?: bool|array{ // Mailer configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         message_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The message bus to use. Defaults to the default bus if the Messenger component is installed. // Default: null
 *         dsn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         transports?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         envelope?: array{ // Mailer Envelope configuration
 *             sender?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             recipients?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             allowed_recipients?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         headers?: array<string, string|array{ // Default: []
 *             value?: mixed,
 *         }>,
 *         dkim_signer?: bool|array{ // DKIM signer configuration
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Key content, or path to key (in PEM format with the `file://` prefix) // Default: ""
 *             domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             select?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             passphrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The private key passphrase // Default: ""
 *             options?: array<string, mixed>,
 *         },
 *         smime_signer?: bool|array{ // S/MIME signer configuration
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Path to key (in PEM format) // Default: ""
 *             certificate?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Path to certificate (in PEM format without the `file://` prefix) // Default: ""
 *             passphrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The private key passphrase // Default: null
 *             extra_certificates?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             sign_options?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: null
 *         },
 *         smime_encrypter?: bool|array{ // S/MIME encrypter configuration
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             repository?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // S/MIME certificate repository service. This service shall implement the `Symfony\Component\Mailer\EventListener\SmimeCertificateRepositoryInterface`. // Default: ""
 *             cipher?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // A set of algorithms used to encrypt the message // Default: null
 *         },
 *     },
 *     secrets?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         vault_directory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.project_dir%/config/secrets/%kernel.runtime_environment%"
 *         local_dotenv_file?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.project_dir%/.env.%kernel.runtime_environment%.local"
 *         decryption_env_var?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "base64:default::SYMFONY_DECRYPTION_SECRET"
 *     },
 *     notifier?: bool|array{ // Notifier configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         message_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The message bus to use. Defaults to the default bus if the Messenger component is installed. // Default: null
 *         chatter_transports?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         texter_transports?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         notification_on_failed_messages?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         channel_policy?: array<string, string|list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>>,
 *         admin_recipients?: list<array{ // Default: []
 *             email?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             phone?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         }>,
 *     },
 *     rate_limiter?: bool|array{ // Rate limiter configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         limiters?: array<string, array{ // Default: []
 *             lock_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of the lock factory used by this limiter (or null to disable locking). // Default: "auto"
 *             cache_pool?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The cache pool to use for storing the current limiter state. // Default: "cache.rate_limiter"
 *             storage_service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of a custom storage implementation, this precedes any configured "cache_pool". // Default: null
 *             policy: "fixed_window"|"token_bucket"|"sliding_window"|"compound"|"no_limit"|\Symfony\Component\Config\Loader\ParamConfigurator, // The algorithm to be used by this limiter.
 *             limiters?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             limit?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum allowed hits in a fixed interval or burst.
 *             interval?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Configures the fixed interval if "policy" is set to "fixed_window" or "sliding_window". The value must be a number followed by "second", "minute", "hour", "day", "week" or "month" (or their plural equivalent).
 *             rate?: array{ // Configures the fill rate if "policy" is set to "token_bucket".
 *                 interval?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Configures the rate interval. The value must be a number followed by "second", "minute", "hour", "day", "week" or "month" (or their plural equivalent).
 *                 amount?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Amount of tokens to add each interval. // Default: 1
 *             },
 *         }>,
 *     },
 *     uid?: bool|array{ // Uid configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         default_uuid_version?: 7|6|4|1|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 7
 *         name_based_uuid_version?: 5|3|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 5
 *         name_based_uuid_namespace?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         time_based_uuid_version?: 7|6|1|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 7
 *         time_based_uuid_node?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     },
 *     html_sanitizer?: bool|array{ // HtmlSanitizer configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         sanitizers?: array<string, array{ // Default: []
 *             allow_safe_elements?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allows "safe" elements and attributes. // Default: false
 *             allow_static_elements?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allows all static elements and attributes from the W3C Sanitizer API standard. // Default: false
 *             allow_elements?: array<string, mixed>,
 *             block_elements?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             drop_elements?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             allow_attributes?: array<string, mixed>,
 *             drop_attributes?: array<string, mixed>,
 *             force_attributes?: array<string, array<string, string|\Symfony\Component\Config\Loader\ParamConfigurator>>,
 *             force_https_urls?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Transforms URLs using the HTTP scheme to use the HTTPS scheme instead. // Default: false
 *             allowed_link_schemes?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             allowed_link_hosts?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>|null,
 *             allow_relative_links?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allows relative URLs to be used in links href attributes. // Default: false
 *             allowed_media_schemes?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             allowed_media_hosts?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>|null,
 *             allow_relative_medias?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allows relative URLs to be used in media source attributes (img, audio, video, ...). // Default: false
 *             with_attribute_sanitizers?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             without_attribute_sanitizers?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             max_input_length?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum length allowed for the sanitized input. // Default: 0
 *         }>,
 *     },
 *     webhook?: bool|array{ // Webhook configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         message_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The message bus to use. // Default: "messenger.default_bus"
 *         routing?: array<string, array{ // Default: []
 *             service: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             secret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         }>,
 *     },
 *     remote-event?: bool|array{ // RemoteEvent configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     json_streamer?: bool|array{ // JSON streamer configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 * }
 * @psalm-type MonologConfig = array{
 *     use_microseconds?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *     channels?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     handlers?: array<string, array{ // Default: []
 *         type: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         priority?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *         level?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "DEBUG"
 *         bubble?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         interactive_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         app_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         include_stacktraces?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         process_psr_3_messages?: array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             date_format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remove_used_context_fields?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         },
 *         path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.logs_dir%/%kernel.environment%.log"
 *         file_permission?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         use_locking?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         filename_format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "{filename}-{date}"
 *         date_format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Y-m-d"
 *         ident?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *         logopts?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 1
 *         facility?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "user"
 *         max_files?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *         action_level?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "WARNING"
 *         activation_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         stop_buffering?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         passthru_level?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         excluded_http_codes?: list<array{ // Default: []
 *             code?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             urls?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *         accepted_levels?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         min_level?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "DEBUG"
 *         max_level?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "EMERGENCY"
 *         buffer_size?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *         flush_on_overflow?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         exchange?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         exchange_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "log"
 *         channel?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         bot_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Monolog"
 *         use_attachment?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *         use_short_attachment?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *         include_extra?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *         icon_emoji?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         webhook_url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         exclude_fields?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         token?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         region?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         source?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         use_ssl?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         user?: mixed,
 *         title?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 514
 *         config?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         members?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         connection_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         timeout?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         time?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 60
 *         deduplication_level?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 400
 *         store?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         connection_timeout?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         persistent?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         message_type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *         parse_mode?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         disable_webpage_preview?: bool|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         disable_notification?: bool|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         split_long_messages?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         delay_between_messages?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         topic?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: null
 *         factor?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 1
 *         tags?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         console_formatter_options?: mixed, // Default: []
 *         formatter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         nested?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         publisher?: string|array{
 *             id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             hostname?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 12201
 *             chunk_size?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 1420
 *             encoder?: "json"|"compressed_json"|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         },
 *         mongodb?: string|array{
 *             id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // ID of a MongoDB\Client service
 *             uri?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             username?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             database?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "monolog"
 *             collection?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "logs"
 *         },
 *         elasticsearch?: string|array{
 *             id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             hosts?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 9200
 *             transport?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Http"
 *             user?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *         index?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "monolog"
 *         document_type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "logs"
 *         ignore_error?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *         redis?: string|array{
 *             id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 6379
 *             database?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *             key_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "monolog_redis"
 *         },
 *         predis?: string|array{
 *             id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         },
 *         from_email?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         to_email?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         subject?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         content_type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         mailer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         email_prototype?: string|array{
 *             id: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             method?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *         verbosity_levels?: array{
 *             VERBOSITY_QUIET?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "ERROR"
 *             VERBOSITY_NORMAL?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "WARNING"
 *             VERBOSITY_VERBOSE?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "NOTICE"
 *             VERBOSITY_VERY_VERBOSE?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "INFO"
 *             VERBOSITY_DEBUG?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "DEBUG"
 *         },
 *         channels?: string|array{
 *             type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             elements?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *     }>,
 * }
 * @psalm-type SecurityConfig = array{
 *     access_denied_url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     session_fixation_strategy?: "none"|"migrate"|"invalidate"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "migrate"
 *     expose_security_errors?: \Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::None|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::AccountStatus|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::All|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "none"
 *     erase_credentials?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     access_decision_manager?: array{
 *         strategy?: "affirmative"|"consensus"|"unanimous"|"priority"|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         strategy_service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         allow_if_all_abstain?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         allow_if_equal_granted_denied?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     password_hashers?: array<string, string|array{ // Default: []
 *         algorithm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         migrate_from?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         hash_algorithm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Name of hashing algorithm for PBKDF2 (i.e. sha256, sha512, etc..) See hash_algos() for a list of supported algorithms. // Default: "sha512"
 *         key_length?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 40
 *         ignore_case?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         encode_as_base64?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         iterations?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 5000
 *         cost?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: null
 *         memory_cost?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         time_cost?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     }>,
 *     providers?: array<string, array{ // Default: []
 *         id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         chain?: array{
 *             providers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         mongodb?: array{
 *             class: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The full entity class name of your user class.
 *             property?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             manager_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *         memory?: array{
 *             users?: array<string, array{ // Default: []
 *                 password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 roles?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             }>,
 *         },
 *         ldap?: array{
 *             service: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             base_dn: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             search_dn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             search_password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             extra_fields?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             default_roles?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             role_fetcher?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             uid_key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "sAMAccountName"
 *             filter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "({uid_key}={user_identifier})"
 *             password_attribute?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *         lexik_jwt?: array{
 *             class?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Lexik\\Bundle\\JWTAuthenticationBundle\\Security\\User\\JWTUser"
 *         },
 *     }>,
 *     firewalls: array<string, array{ // Default: []
 *         pattern?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         methods?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         security?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         user_checker?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The UserChecker to use when authenticating users in this firewall. // Default: "security.user_checker"
 *         request_matcher?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         access_denied_url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         access_denied_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         entry_point?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // An enabled authenticator name or a service id that implements "Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface".
 *         provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         stateless?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         lazy?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         context?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         logout?: array{
 *             enable_csrf?: bool|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             csrf_token_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "logout"
 *             csrf_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_csrf_token"
 *             csrf_token_manager?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/logout"
 *             target?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             invalidate_session?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             clear_site_data?: list<"*"|"cache"|"cookies"|"storage"|"executionContexts"|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             delete_cookies?: array<string, array{ // Default: []
 *                 path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 secure?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *                 samesite?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 partitioned?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *             }>,
 *         },
 *         switch_user?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_switch_user"
 *             role?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "ROLE_ALLOWED_TO_SWITCH"
 *             target_route?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *         required_badges?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         custom_authenticators?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         login_throttling?: array{
 *             limiter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A service id implementing "Symfony\Component\HttpFoundation\RateLimiter\RequestRateLimiterInterface".
 *             max_attempts?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 5
 *             interval?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "1 minute"
 *             lock_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of the lock factory used by the login rate limiter (or null to disable locking). // Default: null
 *             cache_pool?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // The cache pool to use for storing the limiter state // Default: "cache.rate_limiter"
 *             storage_service?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // The service ID of a custom storage implementation, this precedes any configured "cache_pool" // Default: null
 *         },
 *         x509?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             user?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "SSL_CLIENT_S_DN_Email"
 *             credentials?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "SSL_CLIENT_S_DN"
 *             user_identifier?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "emailAddress"
 *         },
 *         remote_user?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             user?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "REMOTE_USER"
 *         },
 *         jwt?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             authenticator?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "lexik_jwt_authentication.security.jwt_authenticator"
 *         },
 *         login_link?: array{
 *             check_route: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Route that will validate the login link - e.g. "app_login_link_verify".
 *             check_post_only?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // If true, only HTTP POST requests to "check_route" will be handled by the authenticator. // Default: false
 *             signature_properties: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             lifetime?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The lifetime of the login link in seconds. // Default: 600
 *             max_uses?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max number of times a login link can be used - null means unlimited within lifetime. // Default: null
 *             used_link_cache?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Cache service id used to expired links of max_uses is set.
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface.
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface.
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The user provider to load users from.
 *             secret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.secret%"
 *             always_use_default_target_path?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             default_target_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             target_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_target_path"
 *             use_referer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             failure_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_failure_path"
 *         },
 *         form_login?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login_check"
 *             use_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             username_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_username"
 *             password_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_password"
 *             csrf_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_csrf_token"
 *             csrf_token_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "authenticate"
 *             enable_csrf?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             post_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             form_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             always_use_default_target_path?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             default_target_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             target_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_target_path"
 *             use_referer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             failure_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_failure_path"
 *         },
 *         form_login_ldap?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login_check"
 *             use_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             username_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_username"
 *             password_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_password"
 *             csrf_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_csrf_token"
 *             csrf_token_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "authenticate"
 *             enable_csrf?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             post_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             form_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             always_use_default_target_path?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             default_target_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             target_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_target_path"
 *             use_referer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             failure_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_failure_path"
 *             service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "ldap"
 *             dn_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "{user_identifier}"
 *             query_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             search_dn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             search_password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         },
 *         json_login?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login_check"
 *             use_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             username_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "username"
 *             password_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "password"
 *         },
 *         json_login_ldap?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login_check"
 *             use_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             username_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "username"
 *             password_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "password"
 *             service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "ldap"
 *             dn_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "{user_identifier}"
 *             query_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             search_dn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             search_password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         },
 *         access_token?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             realm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             token_extractors?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             token_handler: string|array{
 *                 id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 oidc_user_info?: string|array{
 *                     base_uri: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Base URI of the userinfo endpoint on the OIDC server, or the OIDC server URI to use the discovery (require "discovery" to be configured).
 *                     discovery?: array{ // Enable the OIDC discovery.
 *                         cache?: array{
 *                             id: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Cache service id to use to cache the OIDC discovery configuration.
 *                         },
 *                     },
 *                     claim?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Claim which contains the user identifier (e.g. sub, email, etc.). // Default: "sub"
 *                     client?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // HttpClient service id to use to call the OIDC server.
 *                 },
 *                 oidc?: array{
 *                     discovery?: array{ // Enable the OIDC discovery.
 *                         base_uri: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                         cache?: array{
 *                             id: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Cache service id to use to cache the OIDC discovery configuration.
 *                         },
 *                     },
 *                     claim?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Claim which contains the user identifier (e.g.: sub, email..). // Default: "sub"
 *                     audience: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Audience set in the token, for validation purpose.
 *                     issuers: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                     algorithms: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                     keyset?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // JSON-encoded JWKSet used to sign the token (must contain a list of valid public keys).
 *                     encryption?: bool|array{
 *                         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                         enforce?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // When enabled, the token shall be encrypted. // Default: false
 *                         algorithms: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                         keyset: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // JSON-encoded JWKSet used to decrypt the token (must contain a list of valid private keys).
 *                     },
 *                 },
 *                 cas?: array{
 *                     validation_url: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // CAS server validation URL
 *                     prefix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // CAS prefix // Default: "cas"
 *                     http_client?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // HTTP Client service // Default: null
 *                 },
 *                 oauth2?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             },
 *         },
 *         http_basic?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             realm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Secured Area"
 *         },
 *         http_basic_ldap?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             realm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Secured Area"
 *             service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "ldap"
 *             dn_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "{user_identifier}"
 *             query_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             search_dn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             search_password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         },
 *         remember_me?: array{
 *             secret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.secret%"
 *             service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             user_providers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             catch_exceptions?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             signature_properties?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             token_provider?: string|array{
 *                 service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of a custom remember-me token provider.
 *                 doctrine?: bool|array{
 *                     enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                     connection?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 },
 *             },
 *             token_verifier?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of a custom rememberme token verifier.
 *             name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "REMEMBERME"
 *             lifetime?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 31536000
 *             path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             secure?: true|false|"auto"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             httponly?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             samesite?: null|"lax"|"strict"|"none"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "lax"
 *             always_remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             remember_me_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_remember_me"
 *         },
 *         two_factor?: array{
 *             check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/2fa_check"
 *             post_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             auth_form_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/2fa"
 *             always_use_default_target_path?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             default_target_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             authentication_required_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             auth_code_parameter_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_auth_code"
 *             trusted_parameter_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_trusted"
 *             remember_me_sets_trusted?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *             multi_factor?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             prepare_on_login?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             prepare_on_access_denied?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             enable_csrf?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *             csrf_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_csrf_token"
 *             csrf_token_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "two_factor"
 *             csrf_header?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             csrf_token_manager?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "scheb_two_factor.csrf_token_manager"
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *     }>,
 *     access_control?: list<array{ // Default: []
 *         request_matcher?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         requires_channel?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Use the urldecoded format. // Default: null
 *         host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         port?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: null
 *         ips?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         attributes?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         route?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         methods?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_if?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         roles?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     }>,
 *     role_hierarchy?: array<string, string|list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>>,
 * }
 * @psalm-type KnpuOauth2ClientConfig = array{
 *     http_client?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Service id of HTTP client to use (must implement GuzzleHttp\ClientInterface) // Default: null
 *     http_client_options?: array{
 *         timeout?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         proxy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         verify?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Use only with proxy option set
 *     },
 *     clients?: array<string, array<string, mixed>>,
 * }
 * @psalm-type SchebTwoFactorConfig = array{
 *     persister?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "scheb_two_factor.persister.doctrine"
 *     model_manager_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     security_tokens?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     ip_whitelist?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     ip_whitelist_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "scheb_two_factor.default_ip_whitelist_provider"
 *     two_factor_token_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "scheb_two_factor.default_token_factory"
 *     two_factor_provider_decider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "scheb_two_factor.default_provider_decider"
 *     two_factor_condition?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     code_reuse_cache?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     code_reuse_cache_duration?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 60
 *     code_reuse_default_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     backup_codes?: bool|array{
 *         enabled?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *         manager?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "scheb_two_factor.default_backup_code_manager"
 *     },
 *     google?: bool|array{
 *         enabled?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *         form_renderer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         issuer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         server_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         template?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "@SchebTwoFactor/Authentication/form.html.twig"
 *         digits?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 6
 *         leeway?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 0
 *     },
 *     totp?: bool|array{
 *         enabled?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *         form_renderer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         issuer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         server_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         leeway?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 0
 *         parameters?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         template?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "@SchebTwoFactor/Authentication/form.html.twig"
 *     },
 * }
 * @psalm-type LexikJwtAuthenticationConfig = array{
 *     public_key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key used to sign tokens (useless for HMAC). If not set, the key will be automatically computed from the secret key. // Default: null
 *     additional_public_keys?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     secret_key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key used to sign tokens. It can be a raw secret (for HMAC), a raw RSA/ECDSA key or the path to a file itself being plaintext or PEM. // Default: null
 *     pass_phrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key passphrase (useless for HMAC) // Default: ""
 *     token_ttl?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 3600
 *     allow_no_expiration?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allow tokens without "exp" claim (i.e. indefinitely valid, no lifetime) to be considered valid. Caution: usage of this should be rare. // Default: false
 *     clock_skew?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *     encoder?: array{
 *         service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "lexik_jwt_authentication.encoder.lcobucci"
 *         signature_algorithm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "RS256"
 *     },
 *     user_id_claim?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "username"
 *     token_extractors?: array{
 *         authorization_header?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             prefix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Bearer"
 *             name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Authorization"
 *         },
 *         cookie?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "BEARER"
 *         },
 *         query_parameter?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "bearer"
 *         },
 *         split_cookie?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             cookies?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *     },
 *     remove_token_from_body_when_cookies_used?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *     set_cookies?: array<string, array{ // Default: []
 *         lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The cookie lifetime. If null, the "token_ttl" option value will be used // Default: null
 *         samesite?: "none"|"lax"|"strict"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "lax"
 *         path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *         domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         secure?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *         httpOnly?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *         partitioned?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *         split?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     }>,
 *     api_platform?: bool|array{ // API Platform compatibility: add check_path in OpenAPI documentation.
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The login check path to add in OpenAPI. // Default: null
 *         username_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the username in the JSON body. // Default: null
 *         password_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the password in the JSON body. // Default: null
 *     },
 *     access_token_issuance?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         signature?: array{
 *             algorithm: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The algorithm use to sign the access tokens.
 *             key: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The signature key. It shall be JWK encoded.
 *         },
 *         encryption?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             key_encryption_algorithm: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key encryption algorithm is used to encrypt the token.
 *             content_encryption_algorithm: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key encryption algorithm is used to encrypt the token.
 *             key: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The encryption key. It shall be JWK encoded.
 *         },
 *     },
 *     access_token_verification?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         signature?: array{
 *             header_checkers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             claim_checkers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             mandatory_claims?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             allowed_algorithms?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             keyset: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The signature keyset. It shall be JWKSet encoded.
 *         },
 *         encryption?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             continue_on_decryption_failure?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // If enable, non-encrypted tokens or tokens that failed during decryption or verification processes are accepted. // Default: false
 *             header_checkers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             allowed_key_encryption_algorithms?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             allowed_content_encryption_algorithms?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             keyset: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The encryption keyset. It shall be JWKSet encoded.
 *         },
 *     },
 *     blocklist_token?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         cache?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Storage to track blocked tokens // Default: "cache.app"
 *     },
 * }
 * @psalm-type TwigConfig = array{
 *     form_themes?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     globals?: array<string, array{ // Default: []
 *         id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         value?: mixed,
 *     }>,
 *     autoescape_service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     autoescape_service_method?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     cache?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *     charset?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.charset%"
 *     debug?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "%kernel.debug%"
 *     strict_variables?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "%kernel.debug%"
 *     auto_reload?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     optimizations?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *     default_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The default path used to load templates. // Default: "%kernel.project_dir%/templates"
 *     file_name_pattern?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     paths?: array<string, mixed>,
 *     date?: array{ // The default format options used by the date filter.
 *         format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "F j, Y H:i"
 *         interval_format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%d days"
 *         timezone?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The timezone used when formatting dates, when set to null, the timezone returned by date_default_timezone_get() is used. // Default: null
 *     },
 *     number_format?: array{ // The default format options for the number_format filter.
 *         decimals?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 0
 *         decimal_point?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "."
 *         thousands_separator?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ","
 *     },
 *     mailer?: array{
 *         html_to_text_converter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A service implementing the "Symfony\Component\Mime\HtmlToTextConverter\HtmlToTextConverterInterface". // Default: null
 *     },
 * }
 * @psalm-type TwigExtraConfig = array{
 *     cache?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     html?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     markdown?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     intl?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     cssinliner?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     inky?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     string?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     commonmark?: array{
 *         renderer?: array{ // Array of options for rendering HTML.
 *             block_separator?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             inner_separator?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             soft_break?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         },
 *         html_input?: "strip"|"allow"|"escape"|\Symfony\Component\Config\Loader\ParamConfigurator, // How to handle HTML input.
 *         allow_unsafe_links?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Remove risky link and image URLs by setting this to false. // Default: true
 *         max_nesting_level?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum nesting level for blocks. // Default: 9223372036854775807
 *         max_delimiters_per_line?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum number of strong/emphasis delimiters per line. // Default: 9223372036854775807
 *         slug_normalizer?: array{ // Array of options for configuring how URL-safe slugs are created.
 *             instance?: mixed,
 *             max_length?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 255
 *             unique?: mixed,
 *         },
 *         commonmark?: array{ // Array of options for configuring the CommonMark core extension.
 *             enable_em?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             enable_strong?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             use_asterisk?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             use_underscore?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             unordered_list_markers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         ...<mixed>
 *     },
 * }
 * @psalm-type MercureConfig = array{
 *     hubs?: array<string, array{ // Default: []
 *         url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // URL of the hub's publish endpoint
 *         public_url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // URL of the hub's public endpoint // Default: null
 *         jwt?: string|array{ // JSON Web Token configuration.
 *             value?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // JSON Web Token to use to publish to this hub.
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The ID of a service to call to provide the JSON Web Token.
 *             factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The ID of a service to call to create the JSON Web Token.
 *             publish?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             subscribe?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             secret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The JWT Secret to use.
 *             passphrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The JWT secret passphrase. // Default: ""
 *             algorithm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The algorithm to use to sign the JWT // Default: "hmac.sha256"
 *         },
 *         jwt_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Deprecated: The child node "jwt_provider" at path "mercure.hubs..jwt_provider" is deprecated, use "jwt.provider" instead. // The ID of a service to call to generate the JSON Web Token.
 *         bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Name of the Messenger bus where the handler for this hub must be registered. Default to the default bus if Messenger is enabled.
 *     }>,
 *     default_hub?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     default_cookie_lifetime?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default lifetime of the cookie containing the JWT, in seconds. Defaults to the value of "framework.session.cookie_lifetime". // Default: null
 *     enable_profiler?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: The child node "enable_profiler" at path "mercure.enable_profiler" is deprecated. // Enable Symfony Web Profiler integration.
 * }
 * @psalm-type DebugConfig = array{
 *     max_items?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max number of displayed items past the first level, -1 means no limit. // Default: 2500
 *     min_depth?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Minimum tree depth to clone all the items, 1 is default. // Default: 1
 *     max_string_length?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max length of displayed strings, -1 means no limit. // Default: -1
 *     dump_destination?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A stream URL where dumps should be written to. // Default: null
 *     theme?: "dark"|"light"|\Symfony\Component\Config\Loader\ParamConfigurator, // Changes the color of the dump() output when rendered directly on the templating. "dark" (default) or "light". // Default: "dark"
 * }
 * @psalm-type WebProfilerConfig = array{
 *     toolbar?: bool|array{ // Profiler toolbar configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         ajax_replace?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Replace toolbar on AJAX requests // Default: false
 *     },
 *     intercept_redirects?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     excluded_ajax_paths?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "^/((index|app(_[\\w]+)?)\\.php/)?_wdt"
 * }
 * @psalm-type TeknooSpaceEnterpriseConfig = array<mixed>
 * @psalm-type ConfigType = array{
 *     imports?: ImportsConfig,
 *     parameters?: ParametersConfig,
 *     services?: ServicesConfig,
 *     doctrine_mongodb?: DoctrineMongodbConfig,
 *     di_bridge?: DiBridgeConfig,
 *     east_foundation?: EastFoundationConfig,
 *     teknoo_east_common?: TeknooEastCommonConfig,
 *     teknoo_east_paas?: TeknooEastPaasConfig,
 *     framework?: FrameworkConfig,
 *     monolog?: MonologConfig,
 *     security?: SecurityConfig,
 *     knpu_oauth2_client?: KnpuOauth2ClientConfig,
 *     scheb_two_factor?: SchebTwoFactorConfig,
 *     lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *     twig?: TwigConfig,
 *     twig_extra?: TwigExtraConfig,
 *     mercure?: MercureConfig,
 *     teknoo_space_enterprise?: TeknooSpaceEnterpriseConfig,
 *     "when@dev"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         doctrine_mongodb?: DoctrineMongodbConfig,
 *         di_bridge?: DiBridgeConfig,
 *         east_foundation?: EastFoundationConfig,
 *         teknoo_east_common?: TeknooEastCommonConfig,
 *         teknoo_east_paas?: TeknooEastPaasConfig,
 *         framework?: FrameworkConfig,
 *         monolog?: MonologConfig,
 *         security?: SecurityConfig,
 *         knpu_oauth2_client?: KnpuOauth2ClientConfig,
 *         scheb_two_factor?: SchebTwoFactorConfig,
 *         lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *         twig?: TwigConfig,
 *         twig_extra?: TwigExtraConfig,
 *         mercure?: MercureConfig,
 *         debug?: DebugConfig,
 *         web_profiler?: WebProfilerConfig,
 *         teknoo_space_enterprise?: TeknooSpaceEnterpriseConfig,
 *     },
 *     "when@prod"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         doctrine_mongodb?: DoctrineMongodbConfig,
 *         di_bridge?: DiBridgeConfig,
 *         east_foundation?: EastFoundationConfig,
 *         teknoo_east_common?: TeknooEastCommonConfig,
 *         teknoo_east_paas?: TeknooEastPaasConfig,
 *         framework?: FrameworkConfig,
 *         monolog?: MonologConfig,
 *         security?: SecurityConfig,
 *         knpu_oauth2_client?: KnpuOauth2ClientConfig,
 *         scheb_two_factor?: SchebTwoFactorConfig,
 *         lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *         twig?: TwigConfig,
 *         twig_extra?: TwigExtraConfig,
 *         mercure?: MercureConfig,
 *         teknoo_space_enterprise?: TeknooSpaceEnterpriseConfig,
 *     },
 *     "when@test"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         doctrine_mongodb?: DoctrineMongodbConfig,
 *         di_bridge?: DiBridgeConfig,
 *         east_foundation?: EastFoundationConfig,
 *         teknoo_east_common?: TeknooEastCommonConfig,
 *         teknoo_east_paas?: TeknooEastPaasConfig,
 *         framework?: FrameworkConfig,
 *         monolog?: MonologConfig,
 *         security?: SecurityConfig,
 *         knpu_oauth2_client?: KnpuOauth2ClientConfig,
 *         scheb_two_factor?: SchebTwoFactorConfig,
 *         lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *         twig?: TwigConfig,
 *         twig_extra?: TwigExtraConfig,
 *         mercure?: MercureConfig,
 *         debug?: DebugConfig,
 *         web_profiler?: WebProfilerConfig,
 *         teknoo_space_enterprise?: TeknooSpaceEnterpriseConfig,
 *     },
 *     ...<string, ExtensionType|array{ // extra keys must follow the when@%env% pattern or match an extension alias
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         ...<string, ExtensionType>,
 *     }>
 * }
 */
final class App
{
    /**
     * @param ConfigType $config
     *
     * @psalm-return ConfigType
     */
    public static function config(array $config): array
    {
        return AppReference::config($config);
    }
}

namespace Symfony\Component\Routing\Loader\Configurator;

/**
 * This class provides array-shapes for configuring the routes of an application.
 *
 * Example:
 *
 *     ```php
 *     // config/routes.php
 *     namespace Symfony\Component\Routing\Loader\Configurator;
 *
 *     return Routes::config([
 *         'controllers' => [
 *             'resource' => 'routing.controllers',
 *         ],
 *     ]);
 *     ```
 *
 * @psalm-type RouteConfig = array{
 *     path: string|array<string,string>,
 *     controller?: string,
 *     methods?: string|list<string>,
 *     requirements?: array<string,string>,
 *     defaults?: array<string,mixed>,
 *     options?: array<string,mixed>,
 *     host?: string|array<string,string>,
 *     schemes?: string|list<string>,
 *     condition?: string,
 *     locale?: string,
 *     format?: string,
 *     utf8?: bool,
 *     stateless?: bool,
 * }
 * @psalm-type ImportConfig = array{
 *     resource: string,
 *     type?: string,
 *     exclude?: string|list<string>,
 *     prefix?: string|array<string,string>,
 *     name_prefix?: string,
 *     trailing_slash_on_root?: bool,
 *     controller?: string,
 *     methods?: string|list<string>,
 *     requirements?: array<string,string>,
 *     defaults?: array<string,mixed>,
 *     options?: array<string,mixed>,
 *     host?: string|array<string,string>,
 *     schemes?: string|list<string>,
 *     condition?: string,
 *     locale?: string,
 *     format?: string,
 *     utf8?: bool,
 *     stateless?: bool,
 * }
 * @psalm-type AliasConfig = array{
 *     alias: string,
 *     deprecated?: array{package:string, version:string, message?:string},
 * }
 * @psalm-type RoutesConfig = array{
 *     "when@dev"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     "when@prod"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     "when@test"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     ...<string, RouteConfig|ImportConfig|AliasConfig>
 * }
 */
final class Routes
{
    /**
     * @param RoutesConfig $config
     *
     * @psalm-return RoutesConfig
     */
    public static function config(array $config): array
    {
        return $config;
    }
}
