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
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Account;
use Teknoo\East\Paas\Infrastructures\Doctrine\Object\ODM\Project;
use Teknoo\East\Paas\Recipe\Step\Worker\CheckTimeouts;
use Teknoo\East\Paas\Recipe\Step\Worker\CloneRepository;

use function array_map;
use function array_shift;
use function explode;
use function sort;
use function str_starts_with;
use function trim;

/**
 * @copyright Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @author Richard Déloge <richard@teknoo.software>
 */
trait BrowserCrawlingTrait
{
    /**
     * Refresh link in the header of the account edit pages.
     */
    private const string PAGE_REFRESH_BUTTON = '.top-header a.space-page-refresh';

    public function createCrawler(?string $url = null, ?Response $response = null): Crawler
    {
        $url ??= 'https://' . $this->appHostname . $this->currentUrl;
        $response ??= $this->response;
        $crawler = new Crawler(null, $url);
        $crawler->addContent($response->getContent(), $response->headers->get('Content-Type'));

        return $crawler;
    }

    public function createForm(string $formName, ?Crawler $crawler = null): Form
    {
        $crawler ??= $this->createCrawler();

        return $crawler->filter("[name=\"{$formName}\"]")->form();
    }

    public function findUrlFromRouteInPageAndOpenIt(Crawler $crawler, string $routeName, array $parameters = []): void
    {
        $this->isAFinalResponse();

        $url = $this->getPathFromRoute($routeName, $parameters);
        $node = $crawler->filter("a[href=\"{$url}\"]");

        if (0 === $node->count()) {
            Assert::fail("The route '{$routeName}' with url '{$url}' was not found");
        }

        $this->executeRequest('GET', $url);
    }

    public function getCSRFToken(Crawler $crawler, ?string $formName = null, ?string $fieldName = null): ?string
    {
        $fieldName ??= "{$formName}[_token]";
        $field = $crawler->filter("[name=\"{$fieldName}\"]");

        return $field?->getNode(0)?->attributes->getNamedItem('value')->value;
    }

    public function getFormFieldValue(
        array &$formValues,
        string $dottedField,
        ?array $fieldsList = null,
    ): mixed {
        if (null === $fieldsList) {
            $fieldsList = explode('.', $dottedField);
        }

        $fieldName = array_shift($fieldsList);
        if (empty($fieldsList)) {
            return $formValues[$fieldName] ?? null;
        }

        if (!isset($formValues[$fieldName])) {
            return null;
        }

        return $this->getFormFieldValue(
            formValues: $formValues[$fieldName],
            dottedField: $dottedField,
            fieldsList: $fieldsList,
        );
    }

    #[Then("it obtains a empty account's variables form")]
    public function itObtainsAEmptyAccountsVariablesForm(): void
    {
        $formValues = $this->createForm('account_vars')->getPhpValues();
        Assert::assertArrayNotHasKey('sets', $formValues['account_vars']);
    }

    #[Then('the user obtains the form:')]
    public function theUserObtainsTheForm(TableNode $formFields): void
    {
        $formValues = $this->createForm($this->formName)->getPhpValues();
        foreach ($formFields as $field) {
            $this->validFormFieldValue(
                formValues: $formValues,
                dottedField: $field['field'],
                value: $field['value'],
            );
        }
    }

    #[Then('the form field :field is read only')]
    public function theFormFieldIsReadOnly(string $field): void
    {
        Assert::assertNotNull(
            $this->findFormField($field)->attr('readonly'),
            "The form field '{$field}' is editable",
        );
    }

    #[Then('the form field :field is editable')]
    public function theFormFieldIsEditable(string $field): void
    {
        Assert::assertNull(
            $this->findFormField($field)->attr('readonly'),
            "The form field '{$field}' is read only",
        );
    }

    #[Then('the form field :field is hidden')]
    public function theFormFieldIsHidden(string $field): void
    {
        Assert::assertEquals(
            'hidden',
            $this->findFormField($field)->attr('type'),
            "The form field '{$field}' is not hidden",
        );
    }

    #[Then('the form field :field is disabled')]
    public function theFormFieldIsDisabled(string $field): void
    {
        Assert::assertNotNull(
            $this->findFormField($field)->attr('disabled'),
            "The form field '{$field}' is not disabled",
        );
    }

    #[Then('the form field :field is displayed as a checkbox')]
    public function theFormFieldIsDisplayedAsACheckbox(string $field): void
    {
        Assert::assertEquals(
            'checkbox',
            $this->findFormField($field)->attr('type'),
            "The form field '{$field}' is not displayed as a checkbox",
        );
    }

    #[Then('the form field :field is shown only for the cluster types :types')]
    public function theFormFieldIsShownOnlyForTheClusterTypes(string $field, string $types): void
    {
        $row = $this->findFormField($field)->closest('.cluster-field');
        Assert::assertNotNull(
            $row,
            "The form field '{$field}' is shown for every cluster type",
        );

        $expected = array_map(
            static fn (string $type): string => 'cluster-field-for-' . trim($type),
            explode(',', $types),
        );

        $classes = [];
        foreach (explode(' ', (string) $row->attr('class')) as $class) {
            if (str_starts_with($class, 'cluster-field-for-')) {
                $classes[] = $class;
            }
        }

        sort($expected);
        sort($classes);

        Assert::assertEquals(
            $expected,
            $classes,
            "The form field '{$field}' is not shown for the cluster types '{$types}' only",
        );
    }

    #[Then('the form field :field is labelled :label for the cluster type :type')]
    public function theFormFieldIsLabelledForTheClusterType(string $field, string $label, string $type): void
    {
        $id = (string) $this->findFormField($field)->attr('id');

        $node = $this->createCrawler()->filter("label[for=\"{$id}\"] .cluster-field-for-{$type}");
        Assert::assertCount(
            1,
            $node,
            "The form field '{$field}' has no label for the cluster type '{$type}'",
        );

        Assert::assertEquals($label, trim($node->text()));
    }

    #[Then('the form field :field is shown for every cluster type')]
    public function theFormFieldIsShownForEveryClusterType(string $field): void
    {
        Assert::assertNull(
            $this->findFormField($field)->closest('.cluster-field'),
            "The form field '{$field}' is shown only for some cluster types",
        );
    }

    private function findFormField(string $dottedField): Crawler
    {
        $this->isAFinalResponse();

        $parts = explode('.', $dottedField);
        $name = array_shift($parts);
        foreach ($parts as $part) {
            $name .= "[{$part}]";
        }

        $node = $this->createCrawler()->filter("[name=\"{$name}\"]");
        Assert::assertCount(1, $node, "The form field '{$dottedField}' is missing");

        return $node;
    }

    #[Then('it is redirected to the dashboard')]
    public function itIsRedirectedToTheDashboard(): void
    {
        $this->hasBeenUserRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('space_dashboard'),
            $this->currentUrl,
        );
    }

    #[Then('It has a welcome message with :fullName in the dashboard header')]
    public function itHasAWelcomeMessageWithInTheDashboardHeader(string $fullName): void
    {
        $this->isAFinalResponse();

        $crawler = $this->createCrawler();

        $node = $crawler->filter('h6#welcome-message');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);

        Assert::assertEquals(
            $this->translator->trans('teknoo.space.text.welcome_back', ['user' => $fullName,]),
            $nodeValue,
        );
    }

    #[Then('it must be redirected to the TOTP code page')]
    public function itMustBeRedirectedToTheTotpCodePage(): void
    {
        $this->hasBeenUserRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('2fa_login'),
            $this->currentUrl,
        );
    }

    #[Then('it must have a TOTP error')]
    public function itMustHaveATotpError(): void
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('p#2fa-error');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);

        Assert::assertNotEmpty($nodeValue);
    }

    /**
     * An anonymous visitor bounced by the firewall lands on the login form without any `#login-error`
     * node, so the assertion below cannot require one.
     */
    #[Then('it is redirected to the login page')]
    public function itIsRedirectedToTheLoginPage(): void
    {
        $this->hasBeenUserRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('space_account_login'),
            $this->currentUrl,
        );
    }

    #[Then('it is redirected to the login page with an error')]
    public function itIsRedirectedToTheLoginPageWithAnError(): void
    {
        $this->hasBeenUserRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('space_account_login'),
            $this->currentUrl,
        );

        $crawler = $this->createCrawler();
        $node = $crawler->filter('#login-error');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);

        Assert::assertNotEmpty($nodeValue);
    }

    #[When('It goes to projects list page')]
    public function itGoesToProjectsListPage(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_project_list',
        );
    }

    #[Then('the user obtains a project list:')]
    public function theUserObtainsAProjectList(TableNode $projects): void
    {
        $this->isAFinalResponse();

        $crawler = $this->createCrawler();

        $final = [];
        $nodes = $crawler->filter('.space-project-name');
        foreach ($nodes as $node) {
            $final[] = [
                trim((string)$node?->textContent),
            ];
        }

        $expectedProjects = $projects->getRows();
        array_shift($expectedProjects);

        Assert::assertEquals(
            $expectedProjects,
            $final,
        );
    }

    #[Then("it obtains a empty project's form")]
    public function itObtainsAEmptyProjectsForm(): void
    {
        $formValues = $this->createForm('space_project')->getPhpValues();
        Assert::assertEmpty($formValues['space_project']['project']['name']);
    }

    #[Then('the user must have a :code error')]
    public function theUserMustHaveAError(int $code): void
    {
        Assert::assertEquals($code, $this->response?->getStatusCode());
    }

    #[Then("it obtains a empty project's variables form")]
    public function itObtainsAEmptyProjectsVariablesForm(): void
    {
        $formValues = $this->createForm('project_vars')->getPhpValues();
        Assert::assertArrayNotHasKey('sets', $formValues['project_vars']);
    }

    #[Then('it obtains a deployment page')]
    public function itObtainsADeploymentPage(): void
    {
        $this->hasBeenUserRedirected();
        Assert::assertStringStartsWith(
            '/job/pending/',
            $this->currentUrl,
        );
    }

    #[Then('it is forwared to job page')]
    public function itIsForwaredToJobPage(): void
    {
        Assert::assertEquals(
            $this->recall(Project::class),
            $this->firstJob()->getProject(),
        );

        $this->openPageOfFirstJob('space_job_get');
    }

    private function openPageOfFirstJob(string $route): void
    {
        $this->executeRequest(
            'GET',
            $this->getPathFromRoute(
                route: $route,
                parameters: [
                    'id' => $this->firstJob()->getId(),
                ],
            ),
        );
    }

    #[Then('the user obtains an error')]
    public function theUserObtainsAnError(): void
    {
        $this->isAFinalResponse();

        $crawler = $this->createCrawler();
        $node = $crawler->filter('.space-form-error');

        Assert::assertNotEmpty(
            trim((string) $node->getNode(0)?->textContent),
        );
    }

    #[Then('a password mismatch error')]
    public function aPasswordMismatchError(): void
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-error');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);

        Assert::assertEquals(
            $this->translator->trans('The password fields must match.'),
            $nodeValue,
        );
    }

    #[Then('an invalid code error')]
    public function anInvalidCodeError(): void
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-error');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);

        Assert::assertEquals(
            $this->translator->trans('teknoo.space.error.code_not_accepted'),
            $nodeValue,
        );
    }

    #[Then('the user is redirected to the dashboard page')]
    public function theUserIsRedirectedToTheDashboardPage(): void
    {
        $this->hasBeenUserRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('space_dashboard'),
            $this->currentUrl,
        );
    }

    #[Then('get a valid web page')]
    public function getAValidWebOage(): void
    {
        $this->isAFinalResponse();
    }

    /**
     * A single refresh link, reloading the current url with a GET.
     */
    private function assertRefreshButton(Crawler $crawler, string $selector): void
    {
        $node = $crawler->filter($selector);
        Assert::assertCount(1, $node);
        Assert::assertEquals($this->currentUrl, $node->attr('href'));
    }

    #[Then('the page has a refresh button')]
    public function thePageHasARefreshButton(): void
    {
        $this->isAFinalResponse();

        $this->assertRefreshButton($this->createCrawler(), self::PAGE_REFRESH_BUTTON);
    }

    #[When('it refreshes the page')]
    public function itRefreshesThePage(): void
    {
        $this->isAFinalResponse();

        $node = $this->createCrawler()->filter(self::PAGE_REFRESH_BUTTON);
        if (0 === $node->count()) {
            Assert::fail('The refresh button was not found in the page');
        }

        $this->executeRequest('GET', (string) $node->attr('href'));
    }

    #[Then('the account history is displayed with a refresh button')]
    public function theAccountHistoryIsDisplayedWithARefreshButton(): void
    {
        $this->isAFinalResponse();

        $crawler = $this->createCrawler();
        //Hidden by space.css while the accordion button is collapsed, so it must stay its next sibling
        $this->assertRefreshButton(
            $crawler,
            '#headingHistory .accordion-button.collapsed + .space-history-refresh a.space-page-refresh',
        );
        Assert::assertGreaterThan(0, $crawler->filter('#collapseHistory tbody tr')->count());
    }

    #[Then('the account history is not displayed')]
    public function theAccountHistoryIsNotDisplayed(): void
    {
        $this->isAFinalResponse();

        Assert::assertCount(0, $this->createCrawler()->filter('#account-history'));
    }

    #[When('it goes to the admin job page')]
    public function itGoesToTheAdminJobPage(): void
    {
        $this->openPageOfFirstJob('space_admin_job_get');
    }

    #[Then('the job history is humanized on the job page')]
    public function theJobHistoryIsHumanizedOnTheJobPage(): void
    {
        $this->isAFinalResponse();

        ['labels' => $labels, 'warnings' => $warnings] = $this->readHumanizedHistoryRows($this->createCrawler());

        Assert::assertSame('Cloning the source repository', $labels[CloneRepository::class] ?? null);
        Assert::assertSame(
            'Some timeouts exceed the worker time limit',
            $labels[CheckTimeouts::class . ':Warning'] ?? null,
        );

        Assert::assertSame([CheckTimeouts::class . ':Warning'], $warnings);
    }

    #[Then('the account name is now :accountName')]
    public function theAccountNameIsNow(string $accountName): void
    {
        $this->isAFinalResponse();

        if ($this->isApiCall) {
            $account = $this->recall(Account::class);
            Assert::assertEquals(
                $accountName,
                (string) $account,
            );

            return;
        }

        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);
        Assert::assertEquals(
            $this->translator->trans('teknoo.space.alert.data_saved'),
            $nodeValue,
        );

        $node = $crawler->filter('small#space-account-name');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);

        Assert::assertEquals(
            $accountName,
            $nodeValue,
        );
    }

    #[Then('its name is now :fullName')]
    #[Then("the user's name is now :fullName")]
    public function itsNameIsNow(string $fullName): void
    {
        $this->isAFinalResponse();

        if ($this->isApiCall) {
            $user = $this->recall(User::class);
            Assert::assertEquals(
                $fullName,
                $user?->getFirstName() . ' ' . $user?->getLastName(),
            );

            return;
        }

        $crawler = $this->createCrawler();

        $node = $crawler->filter('.space-form-success');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);
        Assert::assertEquals(
            $this->translator->trans('teknoo.space.alert.data_saved'),
            $nodeValue,
        );

        $node = $crawler->filter('span#space-user-name');
        $nodeValue = trim((string) $node->getNode(0)?->textContent);

        Assert::assertEquals(
            $fullName,
            $nodeValue,
        );
    }

    #[Then('The client must go to recovery request sent page')]
    public function theClientMustGoToRecoveryRequestSentPage(): void
    {
        Assert::assertStringStartsWith(
            $this->getPathFromRoute('_teknoo_common_user_recovery'),
            $this->currentUrl,
        );
    }

    #[Then('it is redirected to the recovery password page')]
    public function itIsRedirectedToTheRecoveryPasswordPage(): void
    {
        $this->hasBeenUserRedirected();
        Assert::assertEquals(
            $this->getPathFromRoute('space_update_password'),
            $this->currentUrl,
        );
    }

    #[Then('in the page, the subscription plan is :name')]
    public function intThePageTheSubscriptionPlanNameIs(string $name): void
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('.plan-name');
        $nodeValue = trim((string) $node->getNode(0)?->attributes->getNamedItem('placeholder')->value);
        Assert::assertEquals($name, $nodeValue);
    }

    #[Then('there are :allowed allowed environments and :counted created')]
    public function thereAreAllowedEnvironmentsAndCreated(int $allowed, int $counted): void
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('.environments .allowed');
        $nodeValue = trim((string) $node->getNode(0)?->attributes->getNamedItem('placeholder')->value);
        Assert::assertEquals($allowed, $nodeValue);

        $node = $crawler->filter('.environments .counted');
        $nodeValue = trim((string) $node->getNode(0)?->attributes->getNamedItem('placeholder')->value);
        Assert::assertEquals($counted, $nodeValue);
    }

    #[Then('there are not exceeding environments')]
    public function thereAreNotExceedingEnvironments(): void
    {
        $crawler = $this->createCrawler();
        Assert::assertCount(0, $crawler->filter('.environments.border-danger'));
    }

    #[Then('there are exceeding environments')]
    public function thereAreExceedingEnvironments(): void
    {
        $crawler = $this->createCrawler();
        Assert::assertGreaterThan(0, $crawler->filter('.environments.border-danger')->count());
    }

    #[Then('there are :allowed allowed projects and :counter created')]
    public function thereAreAllowedProjectsAndCreated(int $allowed, int $counted): void
    {
        $crawler = $this->createCrawler();

        $node = $crawler->filter('.projects .allowed');
        $nodeValue = trim((string) $node->getNode(0)?->attributes->getNamedItem('placeholder')->value);
        Assert::assertEquals($allowed, $nodeValue);

        $node = $crawler->filter('.projects .counted');
        $nodeValue = trim((string) $node->getNode(0)?->attributes->getNamedItem('placeholder')->value);
        Assert::assertEquals($counted, $nodeValue);
    }

    #[Then('there are not exceeding projects')]
    public function thereAreNotExceedingProjects(): void
    {
        $crawler = $this->createCrawler();
        Assert::assertCount(0, $crawler->filter('.projects.border-danger'));
    }

    #[Then('there are exceeding projects')]
    public function thereAreExceedingProjects(): void
    {
        $crawler = $this->createCrawler();
        Assert::assertGreaterThan(0, $crawler->filter('.projects.border-danger')->count());
    }

    #[When('It goes to account settings')]
    public function itGoesToAccountSettings(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_account_settings',
        );

        $this->formName = 'space_account';
    }

    #[When('It goes to account status')]
    public function itGoesToAccountStatuss(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_account_status',
        );
    }

    #[When('open the account variables page')]
    public function openTheAccountVariablesPage(): void
    {
        $this->findUrlFromRouteInPageAndOpenIt(
            crawler: $this->createCrawler(),
            routeName: 'space_account_edit_variables',
        );

        $this->formName = 'account_vars';
    }
}
