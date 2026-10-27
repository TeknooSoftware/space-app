@web @admin
Feature: Web admin interface to manage users
  In order to manage the users of Space
  As an administrator of Space
  I want to create, edit and delete users, without their password

  An administrator of Space manages the users, but it can not set the password of an other user: a user created by the
  administration has no password, it signs in through the recovery page or a third party (OAuth). An administrator of
  Space is never a user of an account: a user of an account can not get the admin role. The pending role is for a user
  attached to no account: it is alone, and a user of an account can not get it.

  Background:
    Given a Space app instance
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And a user, called "Albert" "Jean" with the "albert@teknoo.space" with the password "Test2@Test"
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    And it is redirected to the dashboard

  Scenario: From the UI, as Admin, the forms of a user have no password field
    When it goes to the admin page of the user "dupont@teknoo.space"
    Then the form has no field "admin_space_user.user.storedPassword"
    When it goes to the admin page of a new user
    Then the form has no field "admin_space_user.user.storedPassword"

  Scenario: From the UI, as Admin, a password sent in the edit of a user is refused
    When it goes to the admin page of the user "dupont@teknoo.space"
    And it submits the form:
      | field                                                | value               |
      | admin_space_user._token                              | <auto>              |
      | admin_space_user.user.firstName                      | Dupont              |
      | admin_space_user.user.lastName                       | Jean                |
      | admin_space_user.user.email                          | dupont@teknoo.space |
      | admin_space_user.user.active                         | 1                   |
      | admin_space_user.user.roles.0                        | ROLE_USER           |
      | admin_space_user.user.storedPassword.password.first  | NewPass@Test1       |
      | admin_space_user.user.storedPassword.password.second | NewPass@Test1       |
    Then the last request has not saved the user
    When the user logs out
    And the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it is redirected to the dashboard

  Scenario: From the UI, as Admin, create a user without password
    When it goes to the admin page of a new user
    And it submits the form:
      | field                           | value               |
      | admin_space_user._token         | <auto>              |
      | admin_space_user.user.firstName | Behat               |
      | admin_space_user.user.lastName  | Test                |
      | admin_space_user.user.email     | behat@teknoo.space  |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.roles.0   | ROLE_USER           |
    Then get a valid web page
    And the user "behat@teknoo.space" exists without password

  Scenario: From the UI, as Admin, a user of an account can not get the admin role
    When it goes to the admin page of the user "dupont@teknoo.space"
    And it submits the form:
      | field                           | value               |
      | admin_space_user._token         | <auto>              |
      | admin_space_user.user.firstName | Dupont              |
      | admin_space_user.user.lastName  | Jean                |
      | admin_space_user.user.email     | dupont@teknoo.space |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.roles.0   | ROLE_USER           |
      | admin_space_user.user.roles.1   | ROLE_ADMIN          |
    Then the form shows the error "teknoo.space.error.space_user.admin_in_account"
    And the last request has not saved the user

  Scenario: From the UI, as Admin, a user without account can get the admin role
    When it goes to the admin page of the user "albert@teknoo.space"
    And it submits the form:
      | field                           | value               |
      | admin_space_user._token         | <auto>              |
      | admin_space_user.user.firstName | Albert              |
      | admin_space_user.user.lastName  | Jean                |
      | admin_space_user.user.email     | albert@teknoo.space |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.roles.0   | ROLE_USER           |
      | admin_space_user.user.roles.1   | ROLE_ADMIN          |
    Then the last request has saved the user

  Scenario: From the UI, as Admin, an administrator of Space who is a user of an account can only be demoted
    Given the last user has also the role "ROLE_ADMIN"
    When it goes to the admin page of the user "dupont@teknoo.space"
    And it submits the form:
      | field                           | value               |
      | admin_space_user._token         | <auto>              |
      | admin_space_user.user.firstName | Dupont              |
      | admin_space_user.user.lastName  | Jean                |
      | admin_space_user.user.email     | dupont@teknoo.space |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.roles.0   | ROLE_USER           |
      | admin_space_user.user.roles.1   | ROLE_ADMIN          |
    Then the form shows the error "teknoo.space.error.space_user.admin_in_account"
    And the last request has not saved the user
    When it goes to the admin page of the user "dupont@teknoo.space"
    And it submits the form:
      | field                           | value               |
      | admin_space_user._token         | <auto>              |
      | admin_space_user.user.firstName | Dupont              |
      | admin_space_user.user.lastName  | Jean                |
      | admin_space_user.user.email     | dupont@teknoo.space |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.roles.0   | ROLE_USER           |
    Then the last request has saved the user

  Scenario: From the UI, as Admin, create a user with the pending role
    When it goes to the admin page of a new user
    And it submits the form:
      | field                           | value               |
      | admin_space_user._token         | <auto>              |
      | admin_space_user.user.firstName | Behat               |
      | admin_space_user.user.lastName  | Test                |
      | admin_space_user.user.email     | behat@teknoo.space  |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.roles.0   | ROLE_PENDING        |
    Then get a valid web page
    And the user "behat@teknoo.space" has the roles "ROLE_PENDING"

  Scenario: From the UI, as Admin, the pending role can not be given with an other role
    When it goes to the admin page of the user "albert@teknoo.space"
    And it submits the form:
      | field                           | value               |
      | admin_space_user._token         | <auto>              |
      | admin_space_user.user.firstName | Albert              |
      | admin_space_user.user.lastName  | Jean                |
      | admin_space_user.user.email     | albert@teknoo.space |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.roles.0   | ROLE_USER           |
      | admin_space_user.user.roles.1   | ROLE_PENDING        |
    Then the form shows the error "teknoo.space.error.space_user.pending_alone"
    And the last request has not saved the user

  Scenario: From the UI, as Admin, a user without account can get the pending role
    When it goes to the admin page of the user "albert@teknoo.space"
    And it submits the form:
      | field                           | value               |
      | admin_space_user._token         | <auto>              |
      | admin_space_user.user.firstName | Albert              |
      | admin_space_user.user.lastName  | Jean                |
      | admin_space_user.user.email     | albert@teknoo.space |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.roles.0   | ROLE_PENDING        |
    Then the last request has saved the user
    And the user "albert@teknoo.space" has the roles "ROLE_PENDING"

  Scenario: From the UI, as Admin, a user of an account can not get the pending role
    When it goes to the admin page of the user "dupont@teknoo.space"
    And it submits the form:
      | field                           | value               |
      | admin_space_user._token         | <auto>              |
      | admin_space_user.user.firstName | Dupont              |
      | admin_space_user.user.lastName  | Jean                |
      | admin_space_user.user.email     | dupont@teknoo.space |
      | admin_space_user.user.active    | 1                   |
      | admin_space_user.user.roles.0   | ROLE_PENDING        |
    Then the form shows the error "teknoo.space.error.space_user.pending_in_account"
    And the last request has not saved the user
