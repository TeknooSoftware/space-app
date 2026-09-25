@api @admin
Feature: API admin endpoints to administrate accounts
  In order to manage accounts
  As an administrator of Space
  I want to manage accounts.

  On space, Non admin users are mandatory attached to an account. The account is central, projects, users, environments
  and clusters are attached to account. An account can represent a company, a company's unit, a project teams,
  any thing.

  Background:
    Given a Space app instance

  Scenario: From the API, as Admin, list of accounts
    Given a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And 10 accounts with some users
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to list of accounts as admin
    Then get a JSON response
    And is a serialized collection of "10" items on "1" pages
    And the list of serialized accounts

  Scenario: From the API, as Admin, create an account, via a request with a form url encoded body
    Given a kubernetes client
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to create an account as admin:
      | field                                         | value         |
      | admin_space_account.account.name              | Test Behat    |
      | admin_space_account.account.prefix_namespace  | space-client- |
      | admin_space_account.account.namespace         | behat         |
      | admin_space_account.accountData.legalName     | sasu demo     |
      | admin_space_account.accountData.streetAddress | Auge          |
      | admin_space_account.accountData.zipCode       | 14000         |
      | admin_space_account.accountData.cityName      | Caen          |
      | admin_space_account.accountData.countryName   | France        |
      | admin_space_account.accountData.vatNumber     | FR0102030405  |
    Then get a JSON response
    And the serialized account "Test Behat" for admin
    And there is an account in the memory
    And Space executes the pending tasks
    And a Kubernetes namespace dedicated to registry for "behat" is applied and populated on "Demo Kube Cluster"
    And no Kubernetes manifests have been deleted

  Scenario: From the API, as Admin, create an account with subscription plan, via a request with a form url encoded body
    Given a kubernetes client
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to create an account as admin:
      | field                                            | value         |
      | admin_space_account.account.name                 | Test Behat    |
      | admin_space_account.account.prefix_namespace     | space-client- |
      | admin_space_account.account.namespace            | behat         |
      | admin_space_account.accountData.legalName        | sasu demo     |
      | admin_space_account.accountData.streetAddress    | Auge          |
      | admin_space_account.accountData.zipCode          | 14000         |
      | admin_space_account.accountData.cityName         | Caen          |
      | admin_space_account.accountData.countryName      | France        |
      | admin_space_account.accountData.vatNumber        | FR0102030405  |
      | admin_space_account.accountData.subscriptionPlan | test-1        |
    Then get a JSON response
    And the serialized account "Test Behat" for admin
    And there is an account in the memory
    And with the subscription plan "test-1"
    And Space executes the pending tasks
    And a Kubernetes namespace dedicated to registry for "behat" is applied and populated on "Demo Kube Cluster"
    And no Kubernetes manifests have been deleted

  Scenario: From the API, as Admin, create an account, via a request with a json body
    Given a kubernetes client
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to create an account as admin with a json body:
      | field                     | value         |
      | account.name              | Test Behat    |
      | account.prefix_namespace  | space-client- |
      | account.namespace         | behat         |
      | accountData.legalName     | sasu demo     |
      | accountData.streetAddress | Auge          |
      | accountData.zipCode       | 14000         |
      | accountData.cityName      | Caen          |
      | accountData.countryName   | France        |
      | accountData.vatNumber     | FR0102030405  |
    Then get a JSON response
    And the serialized account "Test Behat" for admin
    And there is an account in the memory
    And Space executes the pending tasks
    And a Kubernetes namespace dedicated to registry for "behat" is applied and populated on "Demo Kube Cluster"
    And no Kubernetes manifests have been deleted

  Scenario: From the API, as Admin, create an account with subscription plan, via a request with a json body
    Given a kubernetes client
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to create an account as admin with a json body:
      | field                        | value         |
      | account.name                 | Test Behat    |
      | account.prefix_namespace     | space-client- |
      | account.namespace            | behat         |
      | accountData.legalName        | sasu demo     |
      | accountData.streetAddress    | Auge          |
      | accountData.zipCode          | 14000         |
      | accountData.cityName         | Caen          |
      | accountData.countryName      | France        |
      | accountData.vatNumber        | FR0102030405  |
      | accountData.subscriptionPlan | test-1        |
    Then get a JSON response
    And the serialized account "Test Behat" for admin
    And there is an account in the memory
    And with the subscription plan "test-1"
    And Space executes the pending tasks
    And a Kubernetes namespace dedicated to registry for "behat" is applied and populated on "Demo Kube Cluster"
    And no Kubernetes manifests have been deleted

  Scenario: From the API, as Admin, get an account
    Given a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to get the last account
    Then get a JSON response
    And the serialized account "My Company" for admin
    And no Kubernetes manifests have been deleted

  Scenario: From the API, as Admin, edit an account, via a request with a form url encoded body
    Given a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to edit the last account:
      | field                                            | value         |
      | admin_space_account.account.name                 | Test Behat    |
      | admin_space_account.account.prefix_namespace     | space-client- |
      | admin_space_account.account.namespace            | behat         |
      | admin_space_account.accountData.legalName        | sasu demo     |
      | admin_space_account.accountData.streetAddress    | Auge          |
      | admin_space_account.accountData.zipCode          | 14000         |
      | admin_space_account.accountData.cityName         | Caen          |
      | admin_space_account.accountData.countryName      | France        |
      | admin_space_account.accountData.vatNumber        | FR0102030405  |
      | admin_space_account.accountData.subscriptionPlan | test-1        |
    Then get a JSON response
    And the serialized account "Test Behat" for admin
    And no Kubernetes manifests have been deleted

  Scenario: From the API, as Admin, edit an account, via a request with a json body
    Given a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to edit the last account with a json body:
      | field                        | value         |
      | account.name                 | Test Behat    |
      | account.prefix_namespace     | space-client- |
      | account.namespace            | behat         |
      | accountData.legalName        | sasu demo     |
      | accountData.streetAddress    | Auge          |
      | accountData.zipCode          | 14000         |
      | accountData.cityName         | Caen          |
      | accountData.countryName      | France        |
      | accountData.vatNumber        | FR0102030405  |
      | accountData.subscriptionPlan | test-1        |
    Then get a JSON response
    And the serialized account "Test Behat" for admin
    And no Kubernetes manifests have been deleted

  Scenario: From the API, as Admin, delete an account
    Given a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last account
    Then get a JSON response
    And the serialized deleted account
    And the account is deleted

  Scenario: From the API, as Admin, delete an account, via a request with DELETE method
    Given a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last account with DELETE method
    Then get a JSON response
    And the serialized deleted account
    And the account is deleted

  Scenario: From the API, as Admin, reinstall account's registry namespace
    Given a memory document database
    And a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to reinstall the account registry
    Then get a JSON response
    And the serialized success result
    And Space executes the pending tasks
    And a Kubernetes namespace dedicated to registry for "my-company" is applied and populated on "Demo Kube Cluster"
    And no Kubernetes manifests have been deleted
    And the old account registry object has been deleted and remplaced

  Scenario: From the API, as Admin, reinstall account's registry when the account owns another registry cluster
    Given a memory document database
    And a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an account clusters "Cluster Company" and a slug "cluster-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to reinstall the account registry
    Then get a JSON response
    And the serialized success result
    And Space executes the pending tasks
    And a Kubernetes namespace dedicated to registry for "my-company" is applied and populated on "Demo Kube Cluster"
    And no Kubernetes manifests have been created on "Cluster Company"
    And no Kubernetes manifests have been deleted
    And the old account registry object has been deleted and remplaced
    And the account registry is recorded on the cluster "Demo Kube Cluster"

  Scenario: From the API, as Admin, reinstall an account's registry that does not record its cluster
    Given a memory document database
    And a kubernetes client
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And the account registry does not record its cluster
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to reinstall the account registry
    Then get a JSON response
    And the serialized success result
    And Space executes the pending tasks
    And a Kubernetes namespace dedicated to registry for "my-company" is applied and populated on "Demo Kube Cluster"
    And no Kubernetes manifests have been deleted
    And the account registry is recorded on the cluster "Demo Kube Cluster"
