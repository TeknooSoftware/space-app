@web @admin
Feature: Web admin interface to manage accounts
  In order to manage accounts
  As an administrator of Space
  I want to refresh the quotas of an account and reinstall its registry from the administration

  The provisioning actions of the administration change the clusters of an account: they are sent with a POST and the
  CSRF token of the session, a link (GET) or a request without the token can not start them.

  Background:
    Given a Space app instance
    And a memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    And it is redirected to the dashboard

  Scenario: From the UI, as Admin, the quota of an account can not be refreshed with a link
    When it calls the admin action "refresh quota" of the last account with the method "GET"
    Then the user must have a 405 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, the quota of an account can not be refreshed without the CSRF token
    When it calls the admin action "refresh quota" of the last account with the method "POST"
    Then the user must have a 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, refresh the quota of an account
    When it calls the admin action "refresh quota" of the last account with the method "POST" and the CSRF token
    Then it is redirected to the admin page of the last account
    And Space executes the pending tasks
    And a Kubernetes manifests dedicated to quota for the last account has been applied
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, the registry of an account can not be reinstalled with a link
    When it calls the admin action "reinstall registry" of the last account with the method "GET"
    Then the user must have a 405 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, the registry of an account can not be reinstalled without the CSRF token
    When it calls the admin action "reinstall registry" of the last account with the method "POST"
    Then the user must have a 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, reinstall the registry of an account
    When it calls the admin action "reinstall registry" of the last account with the method "POST" and the CSRF token
    Then it is redirected to the admin page of the last account
    And Space executes the pending tasks
    And a Kubernetes namespace dedicated to registry for "my-company" is applied and populated on "Demo Kube Cluster"
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, an environment of an account can not be reinstalled from the members area
    When it reinstalls the environment "prod" on "Demo Kube Cluster" with the method "POST" and the CSRF token
    Then the user must have a 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario Outline: From the UI, as Admin, the variables, the clusters and the environments of an account are forbidden
    When it opens the admin page "<route>" of the last account
    Then the user must have a 403 error
    And Space executes the pending tasks
    And no Kubernetes manifests have been created

    Examples:
      | route                                     |
      | space_admin_account_edit_variables        |
      | space_admin_account_clusters_list         |
      | space_admin_account_clusters_new          |
      | space_admin_account_environment_reinstall |
      | _teknoo_common_admin_media_list           |
      | _teknoo_common_admin_media_new            |

  Scenario Outline: From the UI, as Admin, the account pages and the subscription codes are allowed
    When it opens the admin page "<route>" of the last account
    Then get a valid web page

    Examples:
      | route                              |
      | _teknoo_paas_admin_account_edit    |
      | space_admin_account_status         |
      | space_admin_account_code_generator |

  Scenario: From the UI, as Admin, edit the data and the subscription of an account, without its environments
    When it goes to the admin account page
    Then the form has no field "admin_space_account.environments"
    When it submits the form:
      | field                                            | value          |
      | admin_space_account._token                       | <auto>         |
      | admin_space_account.account.name                 | Great Company  |
      | admin_space_account.account.users                | <auto>         |
      | admin_space_account.accountData.legalName        | Gr Company SAS |
      | admin_space_account.accountData.streetAddress    | 123 street     |
      | admin_space_account.accountData.zipCode          | 14000          |
      | admin_space_account.accountData.cityName         | Caen           |
      | admin_space_account.accountData.countryName      | France         |
      | admin_space_account.accountData.vatNumber        | FR0102030405   |
      | admin_space_account.accountData.subscriptionPlan | test-1         |
    Then get a valid web page
    And the last request has saved the account
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, the environments of an account can not be changed from its edit
    When it goes to the admin account page
    And it submits the form:
      | field                                            | value             |
      | admin_space_account._token                       | <auto>            |
      | admin_space_account.account.name                 | Great Company     |
      | admin_space_account.account.users                | <auto>            |
      | admin_space_account.accountData.legalName        | Gr Company SAS    |
      | admin_space_account.accountData.streetAddress    | 123 street        |
      | admin_space_account.accountData.zipCode          | 14000             |
      | admin_space_account.accountData.cityName         | Caen              |
      | admin_space_account.accountData.countryName      | France            |
      | admin_space_account.accountData.vatNumber        | FR0102030405      |
      | admin_space_account.accountData.subscriptionPlan | test-1            |
      | admin_space_account.environments.2.clusterName   | Demo Kube Cluster |
      | admin_space_account.environments.2.envName       | testing           |
    Then the last request has not saved the account
    And Space executes the pending tasks
    And no Kubernetes manifests have been created
    And no Kubernetes manifests have been deleted

  Scenario: From the UI, as Admin, a user attached to no account becomes a user of the account
    Given a pending, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    When it goes to the admin account page
    And it submits the form:
      | field                                            | value                      |
      | admin_space_account._token                       | <auto>                     |
      | admin_space_account.account.name                 | Great Company              |
      | admin_space_account.account.users.0              | <auto:dupont@teknoo.space> |
      | admin_space_account.account.users.1              | <auto:albert@teknoo.space> |
      | admin_space_account.accountData.legalName        | Gr Company SAS             |
      | admin_space_account.accountData.streetAddress    | 123 street                 |
      | admin_space_account.accountData.zipCode          | 14000                      |
      | admin_space_account.accountData.cityName         | Caen                       |
      | admin_space_account.accountData.countryName      | France                     |
      | admin_space_account.accountData.vatNumber        | FR0102030405               |
      | admin_space_account.accountData.subscriptionPlan | test-1                     |
    Then get a valid web page
    And the last request has saved the account
    And the user "albert@teknoo.space" is a user of the last account
    And the user "albert@teknoo.space" has the roles "ROLE_USER"
