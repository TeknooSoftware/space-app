@api @admin
Feature: API admin endpoints to administrate users
  In order to manage users
  As an administrator of Space
  I want to manage users.

  On a space instance, each user can edit its own settings, like its firstname, lastname, email and photo.
  There is only one profile per user, profil and user are not separable. This settings host also the user's password
  and other 2FA authentication.

  Background:
    Given a Space app instance
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user

  Scenario: From the API, as Admin, List of users
    Given an account for "My First Company" with the account namespace "my-first-company"
    And 10 basics users for this account
    And an account for "My Other Company" with the account namespace "my-other-company"
    And 5 basics users for this account
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to list of users as admin
    Then get a JSON response
    And is a serialized collection of "16" items on "1" pages
    And the list of serialized users

  Scenario: From the API, as Admin, create a user via a request with a form url encoded body
    Given the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to create a user as admin:
      | field                           | value               |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.firstName | Behat               |
      | admin_space_user.user.lastName  | Test                |
      | admin_space_user.user.roles.    | ROLE_USER           |
      | admin_space_user.user.email     | dupont@teknoo.space |
    Then get a JSON response
    And the serialized user "Test" "Behat" for admin
    And there is a user in the memory

  Scenario: From the API, as Admin, create a user via a request with a json body
    Given the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to create a user as admin with a json body:
      | field          | value               |
      | user.active    | 1                   |
      | user.firstName | Behat               |
      | user.lastName  | Test                |
      | user.roles.    | ROLE_USER           |
      | user.email     | dupont@teknoo.space |
    Then get a JSON response
    And the serialized user "Test" "Behat" for admin
    And there is a user in the memory

  Scenario: From the API, as Admin, get a user
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to get the last user
    Then get a JSON response
    And the serialized user "Dupont" "Jean" for admin

  Scenario: From the API, as Admin, edit a user via a request with a form url encoded body
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to edit the last user:
      | field                           | value               |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.firstName | Behat               |
      | admin_space_user.user.lastName  | Test                |
      | admin_space_user.user.roles.    | ROLE_USER           |
      | admin_space_user.user.email     | dupont@teknoo.space |
    Then get a JSON response
    And the serialized user "Test" "Behat" for admin

  Scenario: From the API, as Admin, edit a user via a request with a json body
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to edit the last user with a json body:
      | field          | value               |
      | user.active    | 1                   |
      | user.firstName | Behat               |
      | user.lastName  | Test                |
      | user.roles.0   | ROLE_USER           |
      | user.email     | dupont@teknoo.space |
    Then get a JSON response
    And the serialized user "Test" "Behat" for admin

  Scenario: From the API, as Admin, delete a user
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last user
    Then get a JSON response
    And the serialized deleted user
    And the user is deleted

  Scenario: From the API, as Admin, delete a user via a request with DELETE method
    Given an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"
    When the API is called to delete the last user with DELETE method
    Then get a JSON response
    And the serialized deleted user
    And the user is deleted
