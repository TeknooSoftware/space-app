Feature: On a space instance, an API is available to manage its user and account settings

  Scenario: Update my user settings via API
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
    When the API is called to get user's settings
    Then get a JSON reponse
    And the serialized user "Dupont" "Jean"
    When the API is called to update user's settings:
      | field                     | value               |
      | space_user.user.firstName | Albert              |
      | space_user.user.lastName  | Dupont              |
      | space_user.user.email     | dupont@teknoo.space |
    Then the user's name is now "Albert Dupont"
    And no Kubernetes manifests must not be deleted

  Scenario: Update my user settings via API with a json body
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
    When the API is called to get user's settings
    Then get a JSON reponse
    And the serialized user "Dupont" "Jean"
    When the API is called to update user's settings with a json body:
      | field          | value               |
      | user.firstName | Albert              |
      | user.lastName  | Dupont              |
      | user.email     | dupont@teknoo.space |
    Then the user's name is now "Albert Dupont"

  Scenario: Update my account settings via API
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

  Scenario: Update my account settings via API with a json body
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

  Scenario: Update my account's environments via API
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
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
      | field                                                   | value             |
      | space_account.account.name                              | My Company        |
      | space_account.accountData.streetAddress                 | 123 street        |
      | space_account.accountData.zipCode                       | 14000             |
      | space_account.accountData.cityName                      | Caen              |
      | space_account.accountData.countryName                   | France            |
      | space_account.accountData.vatNumber                     | FR0102030405      |
      | space_account.environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName              | prod              |
      | space_account.environmentResumes.2.accountEnvironmentId |                   |
      | space_account.environmentResumes.2.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account "My Company"
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Update my account's environments via API with a json body
    Given A Space app instance
    And A memory document database
    And a kubernetes client
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
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
      | field                                     | value             |
      | account.name                              | My Company        |
      | accountData.legalName                     | Gr Company SAS    |
      | accountData.streetAddress                 | 123 street        |
      | accountData.zipCode                       | 14000             |
      | accountData.cityName                      | Caen              |
      | accountData.countryName                   | France            |
      | accountData.vatNumber                     | FR0102030405      |
      | environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | environmentResumes.1.clusterName          | Demo Kube Cluster |
      | environmentResumes.1.envName              | prod              |
      | environmentResumes.2.accountEnvironmentId |                   |
      | environmentResumes.2.clusterName          | Demo Kube Cluster |
      | environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the serialized account "My Company"
    And a Kubernetes namespace for "my-company-testing" dedicated to "Demo Kube Cluster" is applied and populated
    And a Kubernetes namespaces "space-client-my-company-dev" must be deleted
    And the old account environment account "space-client-my-company-dev" must be deleted

  Scenario: Update my account's read only environments via API
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
      | field                                                   | value             |
      | space_account.account.name                              | My Company        |
      | space_account.accountData.streetAddress                 | 123 street        |
      | space_account.accountData.zipCode                       | 14000             |
      | space_account.accountData.cityName                      | Caen              |
      | space_account.accountData.countryName                   | France            |
      | space_account.accountData.vatNumber                     | FR0102030405      |
      | space_account.environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | space_account.environmentResumes.0.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName              | dev               |
      | space_account.environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName              | testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Update my account's read only environments via API with a json body
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
      | field                                     | value             |
      | account.name                              | My Company        |
      | accountData.legalName                     | Gr Company SAS    |
      | accountData.streetAddress                 | 123 street        |
      | accountData.zipCode                       | 14000             |
      | accountData.cityName                      | Caen              |
      | accountData.countryName                   | France            |
      | accountData.vatNumber                     | FR0102030405      |
      | environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | environmentResumes.0.clusterName          | Demo Kube Cluster |
      | environmentResumes.0.envName              | dev               |
      | environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | environmentResumes.1.clusterName          | Demo Kube Cluster |
      | environmentResumes.1.envName              | testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Update my account and exceed environments allowed via API
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
      | field                                                   | value             |
      | space_account.account.name                              | My Company        |
      | space_account.accountData.streetAddress                 | 123 street        |
      | space_account.accountData.zipCode                       | 14000             |
      | space_account.accountData.cityName                      | Caen              |
      | space_account.accountData.countryName                   | France            |
      | space_account.accountData.vatNumber                     | FR0102030405      |
      | space_account.environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | space_account.environmentResumes.0.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.0.envName              | dev               |
      | space_account.environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | space_account.environmentResumes.1.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.1.envName              | prod              |
      | space_account.environmentResumes.2.accountEnvironmentId |                   |
      | space_account.environmentResumes.2.clusterName          | Demo Kube Cluster |
      | space_account.environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Update my account and exceed environments allowed via API with a json body
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
      | field                                     | value             |
      | account.name                              | My Company        |
      | accountData.legalName                     | Gr Company SAS    |
      | accountData.streetAddress                 | 123 street        |
      | accountData.zipCode                       | 14000             |
      | accountData.cityName                      | Caen              |
      | accountData.countryName                   | France            |
      | accountData.vatNumber                     | FR0102030405      |
      | environmentResumes.0.accountEnvironmentId | <auto:dev>        |
      | environmentResumes.0.clusterName          | Demo Kube Cluster |
      | environmentResumes.0.envName              | dev               |
      | environmentResumes.1.accountEnvironmentId | <auto:prod>       |
      | environmentResumes.1.clusterName          | Demo Kube Cluster |
      | environmentResumes.1.envName              | prod              |
      | environmentResumes.2.accountEnvironmentId |                   |
      | environmentResumes.2.clusterName          | Demo Kube Cluster |
      | environmentResumes.2.envName              | testing           |
    Then get a JSON reponse
    And the user must have a 400 error
    And no Kubernetes manifests must not be created
    And no Kubernetes manifests must not be deleted

  Scenario: Create new account variables via the API
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
    When the API is called to update account's variables:
      | field                                     | value  |
      | account_vars.sets.0.envName               | prod   |
      | account_vars.sets.0.variables.0.id        |        |
      | account_vars.sets.0.variables.0.name      | var1   |
      | account_vars.sets.0.variables.0.wasSecret |        |
      | account_vars.sets.0.variables.0.value     | value1 |
      | account_vars.sets.0.variables.1.id        |        |
      | account_vars.sets.0.variables.1.name      | var2   |
      | account_vars.sets.0.variables.1.secret    | 1      |
      | account_vars.sets.0.variables.1.wasSecret |        |
      | account_vars.sets.0.variables.1.value     | value2 |
      | account_vars.sets.1.envName               | dev    |
      | account_vars.sets.1.variables.0.id        |        |
      | account_vars.sets.1.variables.0.name      | var3   |
      | account_vars.sets.1.variables.0.wasSecret |        |
      | account_vars.sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod        |
      | x  | var2 | 1      | value2 | prod        |
      | x  | var3 | 0      | value3 | dev         |
    And no Kubernetes manifests must not be deleted

  Scenario: Create new account variables via the API with a json body
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
    When the API is called to update account's variables with a json body:
      | field                        | value  |
      | sets.0.envName               | prod   |
      | sets.0.variables.0.id        |        |
      | sets.0.variables.0.name      | var1   |
      | sets.0.variables.0.wasSecret |        |
      | sets.0.variables.0.value     | value1 |
      | sets.0.variables.1.id        |        |
      | sets.0.variables.1.name      | var2   |
      | sets.0.variables.1.secret    | 1      |
      | sets.0.variables.1.wasSecret |        |
      | sets.0.variables.1.value     | value2 |
      | sets.1.envName               | dev    |
      | sets.1.variables.0.id        |        |
      | sets.1.variables.0.name      | var3   |
      | sets.1.variables.0.wasSecret |        |
      | sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod        |
      | x  | var2 | 1      | value2 | prod        |
      | x  | var3 | 0      | value3 | dev         |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | eee | var6 | 1      | value6 | dev         |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update account's variables:
      | field                                        | value    |
      | account_vars.sets.prod.envName               | prod     |
      | account_vars.sets.prod.variables.1.id        | bbb      |
      | account_vars.sets.prod.variables.1.name      | var2     |
      | account_vars.sets.prod.variables.1.secret    | 0        |
      | account_vars.sets.prod.variables.1.wasSecret | 1        |
      | account_vars.sets.prod.variables.1.value     |          |
      | account_vars.sets.prod.variables.3.id        |          |
      | account_vars.sets.prod.variables.3.name      | var5     |
      | account_vars.sets.prod.variables.3.secret    | 0        |
      | account_vars.sets.prod.variables.3.wasSecret | 0        |
      | account_vars.sets.prod.variables.3.value     | value5   |
      | account_vars.sets.dev.envName                | dev      |
      | account_vars.sets.dev.variables.0.name       | var3     |
      | account_vars.sets.dev.variables.0.id         | ddd      |
      | account_vars.sets.dev.variables.0.secret     | 0        |
      | account_vars.sets.dev.variables.0.wasSecret  |          |
      | account_vars.sets.dev.variables.0.value      | value3.1 |
      | account_vars.sets.dev.variables.1.name       | var6     |
      | account_vars.sets.dev.variables.1.id         | eee      |
      | account_vars.sets.dev.variables.1.secret     | 1        |
      | account_vars.sets.dev.variables.1.wasSecret  | 1        |
      | account_vars.sets.dev.variables.1.value      | value7   |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 0      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | eee | var6 | 1      | value7   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | eee | var6 | 1      | value6 | dev         |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update account's variables with a json body:
      | field                           | value    |
      | sets.prod.envName               | prod     |
      | sets.prod.variables.1.id        | bbb      |
      | sets.prod.variables.1.name      | var2     |
      | sets.prod.variables.1.secret    | 0        |
      | sets.prod.variables.1.wasSecret | 1        |
      | sets.prod.variables.1.value     |          |
      | sets.prod.variables.3.id        |          |
      | sets.prod.variables.3.name      | var5     |
      | sets.prod.variables.3.secret    | 0        |
      | sets.prod.variables.3.wasSecret |          |
      | sets.prod.variables.3.value     | value5   |
      | sets.dev.envName                | dev      |
      | sets.dev.variables.0.name       | var3     |
      | sets.dev.variables.0.id         | ddd      |
      | sets.dev.variables.0.secret     | 0        |
      | sets.dev.variables.0.wasSecret  |          |
      | sets.dev.variables.0.value      | value3.1 |
      | sets.dev.variables.1.name       | var6     |
      | sets.dev.variables.1.id         | eee      |
      | sets.dev.variables.1.secret     | 1        |
      | sets.dev.variables.1.wasSecret  | 1        |
      | sets.dev.variables.1.value      | value7   |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 0      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | eee | var6 | 1      | value7   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And no Kubernetes manifests must not be deleted

  Scenario: Create new account variables via the API with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update account's variables:
      | field                                     | value  |
      | account_vars.sets.0.envName               | prod   |
      | account_vars.sets.0.variables.0.id        |        |
      | account_vars.sets.0.variables.0.name      | var1   |
      | account_vars.sets.0.variables.0.wasSecret |        |
      | account_vars.sets.0.variables.0.value     | value1 |
      | account_vars.sets.0.variables.1.id        |        |
      | account_vars.sets.0.variables.1.name      | var2   |
      | account_vars.sets.0.variables.1.secret    | 1      |
      | account_vars.sets.0.variables.1.wasSecret |        |
      | account_vars.sets.0.variables.1.value     | value2 |
      | account_vars.sets.1.envName               | dev    |
      | account_vars.sets.1.variables.0.id        |        |
      | account_vars.sets.1.variables.0.name      | var3   |
      | account_vars.sets.1.variables.0.wasSecret |        |
      | account_vars.sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod        |
      | x  | var2 | 1      | value2 | prod        |
      | x  | var3 | 0      | value3 | dev         |
    And no Kubernetes manifests must not be deleted

  Scenario: Create new account variables via the API with a json body with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update account's variables with a json body:
      | field                        | value  |
      | sets.0.envName               | prod   |
      | sets.0.variables.0.id        |        |
      | sets.0.variables.0.name      | var1   |
      | sets.0.variables.0.wasSecret |        |
      | sets.0.variables.0.value     | value1 |
      | sets.0.variables.1.id        |        |
      | sets.0.variables.1.name      | var2   |
      | sets.0.variables.1.secret    | 1      |
      | sets.0.variables.1.wasSecret |        |
      | sets.0.variables.1.value     | value2 |
      | sets.1.envName               | dev    |
      | sets.1.variables.0.id        |        |
      | sets.1.variables.0.name      | var3   |
      | sets.1.variables.0.wasSecret |        |
      | sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod        |
      | x  | var2 | 1      | value2 | prod        |
      | x  | var3 | 0      | value3 | dev         |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | eee | var6 | 1      | value6 | dev         |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update account's variables:
      | field                                        | value    |
      | account_vars.sets.prod.envName               | prod     |
      | account_vars.sets.prod.variables.1.id        | bbb      |
      | account_vars.sets.prod.variables.1.name      | var2     |
      | account_vars.sets.prod.variables.1.secret    | 1        |
      | account_vars.sets.prod.variables.1.wasSecret | 1        |
      | account_vars.sets.prod.variables.1.value     |          |
      | account_vars.sets.prod.variables.3.id        |          |
      | account_vars.sets.prod.variables.3.name      | var5     |
      | account_vars.sets.prod.variables.3.secret    | 0        |
      | account_vars.sets.prod.variables.3.wasSecret | 0        |
      | account_vars.sets.prod.variables.3.value     | value5   |
      | account_vars.sets.dev.envName                | dev      |
      | account_vars.sets.dev.variables.0.name       | var3     |
      | account_vars.sets.dev.variables.0.id         | ddd      |
      | account_vars.sets.dev.variables.0.secret     | 0        |
      | account_vars.sets.dev.variables.0.wasSecret  |          |
      | account_vars.sets.dev.variables.0.value      | value3.1 |
      | account_vars.sets.dev.variables.1.name       | var6     |
      | account_vars.sets.dev.variables.1.id         | eee      |
      | account_vars.sets.dev.variables.1.secret     | 1        |
      | account_vars.sets.dev.variables.1.wasSecret  | 1        |
      | account_vars.sets.dev.variables.1.value      | value7   |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | eee | var6 | 1      | value7   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API with a json body with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | eee | var6 | 1      | value7 | dev         |
      | eee | var6 | 1      | value6 | dev         |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update account's variables with a json body:
      | field                           | value    |
      | sets.prod.envName               | prod     |
      | sets.prod.variables.1.id        | bbb      |
      | sets.prod.variables.1.name      | var2     |
      | sets.prod.variables.1.secret    | 1        |
      | sets.prod.variables.1.wasSecret | 1        |
      | sets.prod.variables.1.value     |          |
      | sets.prod.variables.3.id        |          |
      | sets.prod.variables.3.name      | var5     |
      | sets.prod.variables.3.secret    | 0        |
      | sets.prod.variables.3.wasSecret |          |
      | sets.prod.variables.3.value     | value5   |
      | sets.dev.envName                | dev      |
      | sets.dev.variables.0.name       | var3     |
      | sets.dev.variables.0.id         | ddd      |
      | sets.dev.variables.0.secret     | 0        |
      | sets.dev.variables.0.wasSecret  |          |
      | sets.dev.variables.0.value      | value3.1 |
      | sets.dev.variables.1.name       | var6     |
      | sets.dev.variables.1.id         | eee      |
      | sets.dev.variables.1.secret     | 1        |
      | sets.dev.variables.1.wasSecret  | 1        |
      | sets.dev.variables.1.value      | value7   |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | eee | var6 | 1      | value7   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And no Kubernetes manifests must not be deleted

  Scenario: Get account variables via the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's variables
    Then get a JSON reponse
    And the serialized accounts variables
    And no Kubernetes manifests must not be deleted