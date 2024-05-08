Feature: On a space instance, an API is available to manage user as admin and integrating it with any platform.
  An admin must has same rights of than the web access

  Scenario: List of users from the API
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My First Company" with the account namespace "my-first-company"
    And 10 basics users for this account
    And an account for "My Other Company" with the account namespace "my-other-company"
    And 5 basics users for this account
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to list of users as admin
    Then get a JSON reponse
    And is a serialized collection of "16" items on "1" pages
    And the a list of serialized users

  Scenario: Create a user from the API
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create an user as admin:
      | field                           | value               |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.firstName | Behat               |
      | admin_space_user.user.lastName  | Test                |
      | admin_space_user.user.roles.    | ROLE_USER           |
      | admin_space_user.user.email     | dupont@teknoo.space |
    Then get a JSON reponse
    And the serialized user "Test" "Behat" for admin
    And there is an user in the memory

  Scenario: Create a user from the API with a json body
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create an user as admin with a json body:
      | field          | value               |
      | user.active    | 1                   |
      | user.firstName | Behat               |
      | user.lastName  | Test                |
      | user.roles.    | ROLE_USER           |
      | user.email     | dupont@teknoo.space |
    Then get a JSON reponse
    And the serialized user "Test" "Behat" for admin
    And there is an user in the memory

  Scenario: Get an user from the API
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to get the last user
    Then get a JSON reponse
    And the serialized user "Dupont" "Jean" for admin

  Scenario: Edit an user from the API
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit the last user:
      | field                           | value               |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.firstName | Behat               |
      | admin_space_user.user.lastName  | Test                |
      | admin_space_user.user.roles.    | ROLE_USER           |
      | admin_space_user.user.email     | dupont@teknoo.space |
    Then get a JSON reponse
    And the serialized user "Test" "Behat" for admin

  Scenario: Edit an user from the API with a json body
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to edit the last user with a json body:
      | field          | value               |
      | user.active    | 1                   |
      | user.firstName | Behat               |
      | user.lastName  | Test                |
      | user.roles.0   | ROLE_USER           |
      | user.email     | dupont@teknoo.space |
    Then get a JSON reponse
    And the serialized user "Test" "Behat" for admin

  Scenario: Delete an user from the API
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last user
    Then get a JSON reponse
    And the serialized deleted user
    And the user is deleted

  Scenario: Delete an user from the API with DELETE method
    Given A Space app instance
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to delete the last user with DELETE method
    Then get a JSON reponse
    And the serialized deleted user
    And the user is deleted
