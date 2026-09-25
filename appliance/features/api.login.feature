@api
Feature: API endpoints to login and get a JWT token
  In order to manage api authentication
  As a user of an account
  I want to login on my account without connect to the web endpoint and get an JWT token

  Background:
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"

  Scenario: From the API, login with user without 2FA and password without TOTP and get a JWT Token
    Given a token "behat-token" with the value "sp_azertyuiop123456789"
    And the platform is booted
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON response
    And a new JWT token is returned
    When the API is called to get user's settings
    Then get a JSON response
    And the serialized user "Dupont" "Jean"

  Scenario: From the API, login with user with 2FA and password without TOTP and get a JWT Token
    Given a token "behat-token" with the value "sp_azertyuiop123456789"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON response
    And a new JWT token is returned
    When the API is called to get user's settings
    Then get a JSON response
    And the serialized user "Dupont" "Jean"

  Scenario: From the API, login with user with 2FA and password, create api token and get a JWT Token
    Given the 2FA authentication is enabled for the last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must be redirected to the TOTP code page
    When the user enter a valid TOTP code
    And create api key "behat-custom"
    And the user logs out
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON response
    And a new JWT token is returned
    When the API is called to get user's settings
    Then get a JSON response
    And the serialized user "Dupont" "Jean"

  Scenario: From the API, login with user with 2FA and password without TOTP, use after expiration and get an 401 error
    Given a token "behat-token" with the value "sp_azertyuiop123456789"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    When the time passes by 40 days
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON response
    And an 401 error about "Invalid credentials."

  Scenario: From the API, login with user with 2FA and password, create api token wait 40 days and get an 401 error
    Given the 2FA authentication is enabled for the last user
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must be redirected to the TOTP code page
    When the user enter a valid TOTP code
    And create api key "behat-custom"
    And the user logs out
    When the time passes by 40 days
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON response
    And an 401 error about "Invalid credentials."
