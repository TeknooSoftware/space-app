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

namespace Teknoo\Space\Tests\Behat\Traits;

use Behat\Gherkin\Node\TableNode;
use Behat\Step\When;
use OTPHP\TOTP;
use PHPUnit\Framework\Assert;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Project;

use function array_shift;
use function explode;
use function substr;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait BrowserActionTrait
{
    private function hasBeenUserRedirected(): void
    {
        Assert::assertTrue($this->hasBeenRedirected);
        $this->hasBeenRedirected = false;
    }

    public function isAFinalResponse(): void
    {
        Assert::assertEquals(200, $this->response?->getStatusCode());
    }

    public function setRequestParameters(
        array &$final,
        string $dottedField,
        mixed $value,
        ?array $fieldsList = null,
    ): void {
        if (null === $fieldsList) {
            $fieldsList = explode('.', $dottedField);
        }

        $fieldName = array_shift($fieldsList);
        if (empty($fieldsList)) {
            $final[$fieldName] = $value;

            return;
        }

        if (!isset($final[$fieldName])) {
            $final[$fieldName] = [];
        }

        $this->setRequestParameters($final[$fieldName], $dottedField, $value, $fieldsList);
    }

    public function validFormFieldValue(
        array &$formValues,
        string $dottedField,
        mixed $value,
        ?array $fieldsList = null,
        string $prefix = '',
    ): void {
        if (null === $fieldsList) {
            $fieldsList = explode('.', $dottedField);
        }

        $fieldName = array_shift($fieldsList);
        if (empty($fieldsList)) {
            if ('id' === $fieldName && 'x' === $value) {
                Assert::assertNotEmpty(
                    $formValues[$fieldName] ?? null,
                    "Invalid value for key {$prefix}{$fieldName}"
                );
            } elseif ('id' === $fieldName && 'x' !== $value) {
                Assert::assertEquals(
                    $value,
                    substr($formValues[$fieldName] ?? '', 0, 3),
                    "Invalid value for key {$prefix}{$fieldName}",
                );
            } else {
                Assert::assertEquals(
                    $value,
                    $formValues[$fieldName] ?? null,
                    "Invalid value for key {$prefix}{$fieldName}",
                );
            }

            return;
        }

        if (!isset($formValues[$fieldName])) {
            Assert::fail("Missing key {$prefix}{$fieldName}");
        }

        $this->validFormFieldValue(
            formValues: $formValues[$fieldName],
            dottedField: $dottedField,
            value: $value,
            fieldsList: $fieldsList,
            prefix: $prefix . $fieldName . '.',
        );
    }

    #[When('the user logs out')]
    public function theUserLogsOut(): void
    {
        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'space_account_logout',
            ),
            clearCookies: true,
        );

        $this->isAFinalResponse();
    }

    #[When('it submits the form:')]
    public function itSubmitsTheForm(TableNode $formFields): void
    {
        Assert::assertNotEmpty($this->formName);

        $form = $this->createForm($this->formName);
        $final = [];
        $formValue = $form->getPhpValues();

        foreach ($formFields as $field) {
            if ('<auto>' === $field['value']) {
                $field['value'] = $this->getFormFieldValue($formValue, $field['field']);
            }

            $this->setRequestParameters($final, $field['field'], $field['value']);
        }

        $this->executeRequest(
            method: $form->getMethod(),
            url: $form->getUri(),
            params: $final
        );
    }

    #[When('the user sign in with :email and the password :password')]
    public function theUserSignInWithAndThePassword(string $email, string $password): void
    {
        $this->executeRequest(
            method: 'GET',
            url: $this->getPathFromRoute(
                route: 'space_account_login',
            ),
        );

        $crawler = $this->createCrawler();
        $token = $this->getCSRFToken(crawler: $crawler, fieldName: '_csrf_token');

        $this->executeRequest(
            method: 'post',
            url: $this->getPathFromRoute(
                route: 'space_account_check',
            ),
            params: [
                '_username' => $email,
                '_password' => $password,
                '_csrf_token' => $token,
            ],
        );
    }

    public function submitTotpCode(string $code): void
    {
        $this->executeRequest(
            method: 'post',
            url: $this->getPathFromRoute(
                route: '2fa_login_check',
            ),
            params: [
                '_auth_code' => $code,
            ],
        );
    }

    #[When('the user enter a valid TOTP code')]
    public function theUserEnterAValidTotpCode(): void
    {
        $this->submitTotpCode(
            code: TOTP::createFromSecret(
                secret: (string) $this->recall(TOTPAuth::class)?->getTopSecret()
            )->now()
        );
    }

    #[When('the user enter a wrong TOTP code')]
    public function theUserEnterAWrongTotpCode(): void
    {
        $this->submitTotpCode(
            code: 'fooBar'
        );
    }

    #[When('It goes to new project page')]
    public function itGoesToNewProjectPage(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_list',
        );

        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_new',
        );

        $this->formName = 'space_project';
    }

    #[When('it opens the project page of :projectName')]
    public function itOpensTheProjectPageOf(string $projectName): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_edit',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->formName = 'space_project';
    }

    #[When('It goes to project page of :projectName of :accountName')]
    public function itGoesToProjectPageOfOf(string $projectName, string $accountName): void
    {
        $this->isAFinalResponse();

        $url = $this->getPathFromRoute(
            route: 'space_project_edit',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->executeRequest('GET', $url);
    }

    #[When('It goes to delete the project :projectName of :accountName')]
    public function itGoesToDeleteTheProjectOf(string $projectName, string $accountName): void
    {
        $this->isAFinalResponse();

        $url = $this->getPathFromRoute(
            route: 'space_project_delete',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->executeRequest('GET', $url);
    }

    #[When('it goes to project page of :projectName')]
    public function itGoesToProjectPageOf(string $projectName): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_edit',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );
    }

    #[When('open the project variables page')]
    public function openTheProjectVariablesPage(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_edit_variables',
            parameters: [
                'id' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->formName = 'project_vars';
    }

    #[When('an user go to subscription page')]
    public function anUserGoToSubscriptionPage(): void
    {
        $url = $this->getPathFromRoute(
            route: 'space_subscription',
        );

        $this->executeRequest('GET', $url);
        $this->formName = 'space_subscription';
    }

    #[When('It goes to user settings')]
    public function itGoesToUserSettings(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_my_settings',
        );

        $this->formName = 'space_user';
    }

    #[When('an user go to recovery request page')]
    public function anUserGoToRecoveryRequestPage(): void
    {
        $url = $this->getPathFromRoute(
            route: '_teknoo_common_user_recovery',
        );

        $this->executeRequest('GET', $url);
        $this->formName = 'email_form';
    }

    #[When('the user click on the link in the notification')]
    public function theUserClickOnTheLinkInTheNotification(): void
    {
        $message = $this->getMailerEvents()->getMessages(null)[0];
        $context = $message->getContext();
        $actionUrl = $context['action_url'] ?? '';

        Assert::assertNotEmpty($actionUrl);

        $this->executeRequest(
            method: 'GET',
            url: $actionUrl,
        );
    }

    #[When('it runs a job')]
    public function itRunsAJob(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_job_new',
            parameters: [
                'projectId' => $this->recall(Project::class)?->getId(),
            ],
        );

        $this->formName = 'new_job';
    }
}
