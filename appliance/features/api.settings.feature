Feature: API endpoints to manage its user's settings
  In order to manage user's setting
  As an user of an account
  I want to manage my own user setting like my email or credentials

  On a space instance, each user can edit its own settings, like its firstname, lastname, email and photo.
  There is only one profile per user, profil and user are not separable. This settings host also the user's password
  and other 2FA authentication.

  Scenario: From the API, update my user settings via a request with a form url encoded body
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

  Scenario: From the API, as Admin, update my user settings via API via a request with a json body
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
