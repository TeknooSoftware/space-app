@web
Feature: Web interface to manage its user's settings
  In order to manage user's setting
  As a user of an account
  I want to manage my own user setting like my email or credentials

  On a space instance, each user can edit its own settings, like its firstname, lastname, email and photo.
  There is only one profile per user, profil and user are not separable. This settings host also the user's password
  and other 2FA authentication.

  Scenario: From the UI, update my user settings
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to user settings
    Then the user obtains the form:
      | field                                          | value               |
      | space_user.user.firstName                      | Jean                |
      | space_user.user.lastName                       | Dupont              |
      | space_user.user.email                          | dupont@teknoo.space |
      | space_user.user.storedPassword.password.first  |                     |
      | space_user.user.storedPassword.password.second |                     |
    When it submits the form:
      | field                                          | value               |
      | space_user._token                              | <auto>              |
      | space_user.user.firstName                      | Albert              |
      | space_user.user.lastName                       | Dupont              |
      | space_user.user.email                          | dupont@teknoo.space |
      | space_user.user.storedPassword.password.first  |                     |
      | space_user.user.storedPassword.password.second |                     |
    Then the user obtains the form:
      | field                                          | value               |
      | space_user.user.firstName                      | Albert              |
      | space_user.user.lastName                       | Dupont              |
      | space_user.user.email                          | dupont@teknoo.space |
      | space_user.user.storedPassword.password.first  |                     |
      | space_user.user.storedPassword.password.second |                     |
    And its name is now "Albert Dupont"
    When it submits the form:
      | field                                          | value               |
      | space_user._token                              | <auto>              |
      | space_user.user.firstName                      | Albert              |
      | space_user.user.lastName                       | Dupont              |
      | space_user.user.email                          | dupont@teknoo.space |
      | space_user.user.storedPassword.password.first  | foo                 |
      | space_user.user.storedPassword.password.second | bar                 |
    Then the user obtains an error
    And a password mismatch error
    And the user obtains the form:
      | field                                          | value               |
      | space_user.user.firstName                      | Albert              |
      | space_user.user.lastName                       | Dupont              |
      | space_user.user.email                          | dupont@teknoo.space |
      | space_user.user.storedPassword.password.first  |                     |
      | space_user.user.storedPassword.password.second |                     |
    When it submits the form:
      | field                                          | value               |
      | space_user._token                              | <auto>              |
      | space_user.user.firstName                      | Albert              |
      | space_user.user.lastName                       | Dupont              |
      | space_user.user.email                          | dupont@teknoo.space |
      | space_user.user.storedPassword.password.first  | Test3@Test          |
      | space_user.user.storedPassword.password.second | Test3@Test          |
    When the user logs out
    Given the user is signed in with "dupont@teknoo.space" and the password "Test3@Test"
    Then it is redirected to the dashboard
    And a new session is open
