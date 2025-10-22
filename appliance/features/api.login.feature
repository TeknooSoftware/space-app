Feature: API endpoints to login and get a JWT token
  In order to manage api authentication
  As an user of an account
  I want to login on my account without connect to the web endpoint and get an JWT token

  Scenario: From the API, login with user without 2FA and password without TOTP and get a JWT Token
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a token "behat-token" with the value "sp_azertyuiop123456789"
    And the platform is booted
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON reponse
    And a new JWT token is returned
    When the API is called to get user's settings
    Then get a JSON reponse
    And the serialized user "Dupont" "Jean"

  Scenario: From the API, login with user with 2FA and password without TOTP and get a JWT Token
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a token "behat-token" with the value "sp_azertyuiop123456789"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON reponse
    And a new JWT token is returned
    When the API is called to get user's settings
    Then get a JSON reponse
    And the serialized user "Dupont" "Jean"

  Scenario: From the API, login with user with 2FA and password, create api token and get a JWT Token
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And create api key "behat-custom"
    And the user logs out
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON reponse
    And a new JWT token is returned
    When the API is called to get user's settings
    Then get a JSON reponse
    And the serialized user "Dupont" "Jean"

  Scenario: From the API, login with user with 2FA and password without TOTP, use after expiration and get an 401 error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a token "behat-token" with the value "sp_azertyuiop123456789"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the time passes by 40 days
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON reponse
    And an 401 error about "Invalid credentials."

  Scenario: From the API, login with user with 2FA and password, create api token wait 40 days and get an 401 error
    Given A Space app instance
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And create api key "behat-custom"
    And the user logs out
    When the time passes by 40 days
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON reponse
    And an 401 error about "Invalid credentials."
