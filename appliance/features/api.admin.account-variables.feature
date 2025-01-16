Feature: On a space instance, an API is available to manage accounts as admin and integrating it with any platform.
  An admin must has same rights of than the web access

  Scenario: Create new account variables via the API as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with as admin:
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

  Scenario: Create new account variables via the API with a json body as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with a json body as admin:
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

  Scenario: Update or delete account variables via the API as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod        |
      | fff | var6 | 1      | value6 | prod        |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | ggg | var6 | 1      | value6 | dev         |
      | hhh | var8 | 1      | value8 | dev         |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with as admin:
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
      | account_vars.sets.dev.variables.1.id         | ggg      |
      | account_vars.sets.dev.variables.1.name       | var6     |
      | account_vars.sets.dev.variables.1.secret     | 1        |
      | account_vars.sets.dev.variables.1.wasSecret  | 1        |
      | account_vars.sets.dev.variables.1.value      | value7   |
      | account_vars.sets.dev.variables.2.id         | hhh      |
      | account_vars.sets.dev.variables.2.name       | var8     |
      | account_vars.sets.dev.variables.2.secret     | 0        |
      | account_vars.sets.dev.variables.2.wasSecret  | 1        |
      | account_vars.sets.dev.variables.2.value      | value8   |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | ggg | var6 | 1      | value7   | dev         |
      | hhh | var8 | 0      | value8   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API with a json body as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod        |
      | fff | var6 | 1      | value6 | prod        |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | ggg | var6 | 1      | value6 | dev         |
      | hhh | var8 | 1      | value8 | dev         |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with a json body as admin:
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
      | sets.dev.variables.1.id         | ggg      |
      | sets.dev.variables.1.secret     | 1        |
      | sets.dev.variables.1.wasSecret  | 1        |
      | sets.dev.variables.1.value      | value7   |
      | sets.dev.variables.2.id         | hhh      |
      | sets.dev.variables.2.name       | var8     |
      | sets.dev.variables.2.secret     | 0        |
      | sets.dev.variables.2.wasSecret  | 1        |
      | sets.dev.variables.2.value      | value8   |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | ggg | var6 | 1      | value7   | dev         |
      | hhh | var8 | 0      | value8   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And no Kubernetes manifests must not be deleted

  Scenario: Create new account variables via the API as Admin with secrets encryptions as Admin
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with as admin:
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

  Scenario: Create new account variables via the API with a json body as Admin with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with a json body as admin:
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

  Scenario: Update or delete account variables via the API as Admin with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod        |
      | fff | var6 | 1      | value6 | prod        |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | ggg | var6 | 1      | value6 | dev         |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with as admin:
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
      | account_vars.sets.dev.variables.1.id         | ggg      |
      | account_vars.sets.dev.variables.1.name       | var6     |
      | account_vars.sets.dev.variables.1.secret     | 1        |
      | account_vars.sets.dev.variables.1.wasSecret  | 1        |
      | account_vars.sets.dev.variables.1.value      | value7   |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | ggg | var6 | 1      | value7   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And no Kubernetes manifests must not be deleted

  Scenario: Update or delete account variables via the API with a json body as Admin with secrets encryptions
    Given A Space app instance
    And A memory document database
    And encryption of persisted variables in the database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod        |
      | fff | var6 | 1      | value6 | prod        |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
      | ggg | var6 | 1      | value6 | dev         |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update variables of last account with a json body as admin:
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
      | sets.dev.variables.1.id         | ggg      |
      | sets.dev.variables.1.secret     | 1        |
      | sets.dev.variables.1.wasSecret  | 1        |
      | sets.dev.variables.1.value      | value7   |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 1      | value2   | prod        |
      | ddd | var3 | 0      | value3.1 | dev         |
      | ggg | var6 | 1      | value7   | dev         |
      | x   | var5 | 0      | value5   | prod        |
    And no Kubernetes manifests must not be deleted

  Scenario: Get account variables via the API as Admin
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | eee | var5 | 0      | value5 | prod        |
      | fff | var6 | 1      | value6 | prod        |
    And an account for "My Other Company" with the account namespace "my-other-company"
    And the account has these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod        |
      | bbb | var2 | 1      | value2 | prod        |
      | ccc | var3 | 0      | value3 | prod        |
      | ddd | var4 | 0      | value4 | dev         |
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get variables of last account as admin
    Then get a JSON reponse
    And the serialized accounts variables with 4 variables
    And no Kubernetes manifests must not be deleted
