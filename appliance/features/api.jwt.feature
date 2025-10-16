Feature: API endpoints to create and refresh new JWT token
  In order to manage api authentication
  As an user of an account
  I want to manage refresh my jwt token

  Scenario: From the API, create a new jwt token via API with a form url encoded body
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
    When the API is called to get a new JWT token
    Then get a JSON reponse
    And a new token is returned
    When the API client switch to new JWT token
    And the API is called to get user's settings
    Then get a JSON reponse
    And the serialized user "Dupont" "Jean"

  Scenario: From the API, create a new jwt token via API via a request with a json body
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
    When the API is called to get a new JWT token with a json body
    Then get a JSON reponse
    And a new token is returned
    When the API client switch to new JWT token
    And the API is called to get user's settings
    Then get a JSON reponse
    And the serialized user "Dupont" "Jean"
