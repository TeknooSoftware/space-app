Feature: API endpoints to manage account's settings
  In order to manage account's setting
  As an user of an account
  I want to manage my account's name and account's legal informations

  On space, Non admin users are mandatory attached to an account. The account is central, projects, users, environments
  and clusters are attached to account. An account can represent a company, a company's unit, a project teams,
  any thing.

  Scenario: From the API, update my account settings, via a request with a form url encoded body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's settings
    Then get a JSON reponse
    And the serialized account "My Company"
    When the API is called to update account's settings:
      | field                                   | value          |
      | space_account.account.name              | Great Company  |
      | space_account.accountData.legalName     | Gr Company SAS |
      | space_account.accountData.streetAddress | 123 street     |
      | space_account.accountData.zipCode       | 14000          |
      | space_account.accountData.cityName      | Caen           |
      | space_account.accountData.countryName   | France         |
      | space_account.accountData.vatNumber     | FR0102030405   |
    And the account name is now "Great Company"
    And no Kubernetes manifests must not be deleted

  Scenario: From the API, update my account settings, via a request with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's settings
    Then get a JSON reponse
    And the serialized account "My Company"
    When the API is called to update account's settings with a json body:
      | field                     | value          |
      | account.name              | Great Company  |
      | accountData.legalName     | Gr Company SAS |
      | accountData.streetAddress | 123 street     |
      | accountData.zipCode       | 14000          |
      | accountData.cityName      | Caen           |
      | accountData.countryName   | France         |
      | accountData.vatNumber     | FR0102030405   |
    And the account name is now "Great Company"
    And no Kubernetes manifests must not be deleted

  Scenario: From the API, get my account status, when all is green
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And "2" standard projects "project X" and a prefix "a-prefix"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's status
    Then get a JSON reponse
    And the subscription plan is "Test Plan 1"
    And with "2" allowed environments and 2 created
    And without exceeding environments
    And with "3" allowed projects and 2 created
    And without exceeding projects
    And no Kubernetes manifests must not be deleted

  Scenario: From the API, get my account status, when it is fully used and all is green
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And "3" standard projects "project X" and a prefix "a-prefix"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's status
    Then get a JSON reponse
    And the subscription plan is "Test Plan 1"
    And with "2" allowed environments and 2 created
    And without exceeding environments
    And with "3" allowed projects and 3 created
    And without exceeding projects
    And no Kubernetes manifests must not be deleted

  Scenario: From the API, get my account status, when there are more projects than allowed
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And "4" standard projects "project X" and a prefix "a-prefix"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's status
    Then get a JSON reponse
    And the subscription plan is "Test Plan 1"
    And with "2" allowed environments and 2 created
    And without exceeding environments
    And with "3" allowed projects and 4 created
    And with exceeding projects
    And no Kubernetes manifests must not be deleted

  Scenario: From the API, get my account status, when there are more environments than allowed
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And an account environment on "Cluster Company" for the environment "prod"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And "2" standard projects "project X" and a prefix "a-prefix"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's status
    Then get a JSON reponse
    And the subscription plan is "Test Plan 1"
    And with "2" allowed environments and 3 created
    And with exceeding environments
    And with "3" allowed projects and 2 created
    And without exceeding projects
    And no Kubernetes manifests must not be deleted
