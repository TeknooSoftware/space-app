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
