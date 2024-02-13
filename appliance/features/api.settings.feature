Feature: On a space instance, an API is available to manage its user and account settings

  Scenario: Update my user settings via API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
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

  Scenario: Update my user settings via API with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
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
    And an account for "My Company" with the account namespace "my-comany"
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

  Scenario: Create new account variables via the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
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
      | account_vars.sets.0.environmentName       | prod1  |
      | account_vars.sets.0.variables.0.id        |        |
      | account_vars.sets.0.variables.0.name      | var1   |
      | account_vars.sets.0.variables.0.wasSecret |        |
      | account_vars.sets.0.variables.0.value     | value1 |
      | account_vars.sets.0.variables.1.id        |        |
      | account_vars.sets.0.variables.1.name      | var2   |
      | account_vars.sets.0.variables.1.secret    | 1      |
      | account_vars.sets.0.variables.1.wasSecret |        |
      | account_vars.sets.0.variables.1.value     | value2 |
      | account_vars.sets.1.environmentName       | prod2  |
      | account_vars.sets.1.variables.0.id        |        |
      | account_vars.sets.1.variables.0.name      | var3   |
      | account_vars.sets.1.variables.0.wasSecret |        |
      | account_vars.sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod1       |
      | x  | var2 | 1      | value2 | prod1       |
      | x  | var3 | 0      | value3 | prod2       |

  Scenario: Create new account variables via the API with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
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
      | sets.0.environmentName       | prod1  |
      | sets.0.variables.0.id        |        |
      | sets.0.variables.0.name      | var1   |
      | sets.0.variables.0.wasSecret |        |
      | sets.0.variables.0.value     | value1 |
      | sets.0.variables.1.id        |        |
      | sets.0.variables.1.name      | var2   |
      | sets.0.variables.1.secret    | 1      |
      | sets.0.variables.1.wasSecret |        |
      | sets.0.variables.1.value     | value2 |
      | sets.1.environmentName       | prod2  |
      | sets.1.variables.0.id        |        |
      | sets.1.variables.0.name      | var3   |
      | sets.1.variables.0.wasSecret |        |
      | sets.1.variables.0.value     | value3 |
    Then the account must have these persisted variables
      | id | name | secret | value  | environment |
      | x  | var1 | 0      | value1 | prod1       |
      | x  | var2 | 1      | value2 | prod1       |
      | x  | var3 | 0      | value3 | prod2       |

  Scenario: Update or delete account variables via the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account have these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod1       |
      | bbb | var2 | 1      | value2 | prod1       |
      | ccc | var3 | 0      | value3 | prod1       |
      | ddd | var4 | 0      | value4 | prod2       |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update account's variables:
      | field                                         | value    |
      | account_vars.sets.prod1.environmentName       | prod1    |
      | account_vars.sets.prod1.variables.1.id        | bbb      |
      | account_vars.sets.prod1.variables.1.name      | var2     |
      | account_vars.sets.prod1.variables.1.secret    | 0        |
      | account_vars.sets.prod1.variables.1.wasSecret | 1        |
      | account_vars.sets.prod1.variables.1.value     |          |
      | account_vars.sets.prod1.variables.3.id        |          |
      | account_vars.sets.prod1.variables.3.name      | var5     |
      | account_vars.sets.prod1.variables.3.secret    | 0        |
      | account_vars.sets.prod1.variables.3.wasSecret | 0        |
      | account_vars.sets.prod1.variables.3.value     | value5   |
      | account_vars.sets.prod2.environmentName       | prod2    |
      | account_vars.sets.prod2.variables.0.name      | var3     |
      | account_vars.sets.prod2.variables.0.id        | ddd      |
      | account_vars.sets.prod2.variables.0.secret    | 0        |
      | account_vars.sets.prod2.variables.0.wasSecret |          |
      | account_vars.sets.prod2.variables.0.value     | value3.1 |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 0      | value2   | prod1       |
      | ddd | var3 | 0      | value3.1 | prod2       |
      | x   | var5 | 0      | value5   | prod1       |

  Scenario: Update or delete account variables via the API with a json body
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account have these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod1       |
      | bbb | var2 | 1      | value2 | prod1       |
      | ccc | var3 | 0      | value3 | prod1       |
      | ddd | var4 | 0      | value4 | prod2       |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to update account's variables with a json body:
      | field                            | value    |
      | sets.prod1.environmentName       | prod1    |
      | sets.prod1.variables.1.id        | bbb      |
      | sets.prod1.variables.1.name      | var2     |
      | sets.prod1.variables.1.secret    | 0        |
      | sets.prod1.variables.1.wasSecret | 1        |
      | sets.prod1.variables.1.value     |          |
      | sets.prod1.variables.3.id        |          |
      | sets.prod1.variables.3.name      | var5     |
      | sets.prod1.variables.3.secret    | 0        |
      | sets.prod1.variables.3.wasSecret |          |
      | sets.prod1.variables.3.value     | value5   |
      | sets.prod2.environmentName       | prod2    |
      | sets.prod2.variables.0.name      | var3     |
      | sets.prod2.variables.0.id        | ddd      |
      | sets.prod2.variables.0.secret    | 0        |
      | sets.prod2.variables.0.wasSecret |          |
      | sets.prod2.variables.0.value     | value3.1 |
    Then the account must have these persisted variables
      | id  | name | secret | value    | environment |
      | bbb | var2 | 0      | value2   | prod1       |
      | ddd | var3 | 0      | value3.1 | prod2       |
      | x   | var5 | 0      | value5   | prod1       |

  Scenario: Get account variables via the API
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the account have these persisted variables:
      | id  | name | secret | value  | environment |
      | aaa | var1 | 0      | value1 | prod1       |
      | bbb | var2 | 1      | value2 | prod1       |
      | ccc | var3 | 0      | value3 | prod1       |
      | ddd | var4 | 0      | value4 | prod2       |
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get account's variables
    Then get a JSON reponse
    And the serialized accounts variables