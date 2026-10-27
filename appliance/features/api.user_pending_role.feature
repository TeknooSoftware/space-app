@api
Feature: API refused to a user attached to no account
  In order to keep the API to the users of an account
  As a user of Space attached to no account
  I can not use the API, even with an API key or a JWT created before

  A user with ROLE_PENDING is attached to no account. The API refuses it, with the JSON error envelope: the login with
  an API key, its settings, the creation of a JWT and the endpoints of the members of an account. The JWT reloads the
  user at each request, so the new role applies at once.

  Background:
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"

  Scenario: From the API, a pending user can not get a JWT with an API key
    Given a token "behat-token" with the value "sp_azertyuiop123456789"
    And the last user has only the role "ROLE_PENDING"
    And the platform is booted
    When the user sign on API in with "dupont@teknoo.space" and the previous token
    Then get a JSON response
    But an 403 error

  Scenario Outline: From the API, the JWT of a user that becomes pending is refused
    Given the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "dupont@teknoo.space" and the password "Test2@Test"
    And the last user has only the role "ROLE_PENDING"
    When the API is called with the method "<method>" on the route "<route>"
    Then get a JSON response
    But an 403 error

    Examples:
      | method | route                         |
      | GET    | space_api_v1_my_settings      |
      | POST   | _teknoo_common_api_jwt_create |
      | GET    | space_api_v1_project_list     |
      | GET    | space_api_v1_account_settings |
