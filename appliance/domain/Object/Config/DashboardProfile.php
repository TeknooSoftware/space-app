<?php

/*
 * Teknoo Space.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/applications/space Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Space\Object\Config;

use InvalidArgumentException;
use SensitiveParameter;

use function htmlspecialchars;
use function preg_match;
use function strtr;

use const ENT_QUOTES;

/**
 * Describes how Space embeds and relays a kind of Kubernetes web dashboard (Headlamp, the legacy Kubernetes
 * Dashboard...), so the relay stays generic and a new dashboard only needs a new profile:
 * - `requestHeaders`: templates of the headers injected in every request relayed to the dashboard, `{token}` is
 *   replaced by the credential of the environment (or of the cluster for an administrator);
 * - `entryPath` / `namespacedEntryPath`: path opened in the frame, for all namespaces (administrator) or for the
 *   namespace (`{namespace}`) of the environment;
 * - `headSnippet` / `namespacedHeadSnippet`: HTML inserted at the start of the `<head>` of the pages relayed
 *   (`{baseHref}`: absolute URL of the frame, `{namespace}`);
 * - `rewriteBasePath`: the dashboard is served under a base path (the path of its address), replaced in the pages
 *   relayed by the path of the frame;
 * - `pathAliases`: paths requested by the browser, relayed to another path of the dashboard.
 *
 * A namespace is always a Kubernetes name (RFC 1123 label), checked before any substitution, so a template can
 * use it in an URL, in HTML or in a JavaScript string.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DashboardProfile
{
    private const string NAMESPACE_PATTERN = '/^[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?$/';

    /**
     * @param array<string, string> $requestHeaders
     * @param array<string, string> $pathAliases
     */
    public function __construct(
        public readonly string $name,
        public readonly array $requestHeaders,
        public readonly string $entryPath = '',
        public readonly string $namespacedEntryPath = '',
        public readonly string $headSnippet = '',
        public readonly string $namespacedHeadSnippet = '',
        public readonly bool $rewriteBasePath = false,
        public readonly array $pathAliases = [],
    ) {
    }

    private function checkNamespace(string $namespace): string
    {
        if (1 !== preg_match(self::NAMESPACE_PATTERN, $namespace)) {
            throw new InvalidArgumentException("The namespace '{$namespace}' is not a valid Kubernetes namespace");
        }

        return $namespace;
    }

    /**
     * @return array<string, string>
     */
    public function renderRequestHeaders(#[SensitiveParameter] string $token): array
    {
        $headers = [];
        foreach ($this->requestHeaders as $name => $template) {
            $headers[$name] = strtr($template, ['{token}' => $token]);
        }

        return $headers;
    }

    public function renderEntryPath(?string $namespace): string
    {
        if (null === $namespace) {
            return $this->entryPath;
        }

        return strtr($this->namespacedEntryPath, ['{namespace}' => $this->checkNamespace($namespace)]);
    }

    public function renderHeadSnippet(string $baseHref, ?string $namespace): string
    {
        $template = $this->headSnippet;
        if (null !== $namespace) {
            $template = $this->namespacedHeadSnippet;
            $namespace = $this->checkNamespace($namespace);
        }

        return strtr(
            $template,
            [
                '{baseHref}' => htmlspecialchars($baseHref, ENT_QUOTES),
                '{namespace}' => (string) $namespace,
            ],
        );
    }

    public function resolvePath(string $path): string
    {
        return $this->pathAliases[$path] ?? $path;
    }
}
