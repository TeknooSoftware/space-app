@api @admin
Feature: API endpoints of the members of an account, refused to the administrators of Space
  In order to keep the projects, the jobs and the settings of an account in the hands of its users
  As an administrator of Space
  I can not use the API endpoints of the members of an account

  An administrator of Space is never a user of an account. It supervises the accounts from the admin endpoints, but it
  can not call the endpoints of the members (projects, jobs, settings, environments, variables and clusters of an
  account). The refusal has the JSON error envelope. Its own settings stay available.

  Background:
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And a standard project "my project" and a prefix "a-prefix"
    And "1" jobs for the project
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"

  Scenario Outline: From the API, as Admin, the endpoints of the members of an account are refused
    When the API is called with the method "<method>" on the route "<route>"
    Then get a JSON response
    But an 403 error

    Examples:
      | method | route                                    |
      | GET    | space_api_v1_account_settings            |
      | GET    | space_api_v1_account_status              |
      | GET    | space_api_v1_account_environments        |
      | GET    | space_api_v1_account_edit_variables      |
      | GET    | space_api_v1_account_clusters_list       |
      | POST   | space_api_v1_account_clusters_new        |
      | GET    | space_api_v1_account_clusters_edit       |
      | POST   | space_api_v1_account_clusters_delete     |
      | POST   | space_api_v1_project_new                 |
      | GET    | space_api_v1_project_edit                |
      | GET    | space_api_v1_project_edit_variables      |
      | POST   | space_api_v1_project_refresh_credentials |
      | GET    | space_api_v1_project_list                |
      | POST   | space_api_v1_project_delete              |
      | POST   | space_api_v1_job_new                     |
      | GET    | space_api_v1_job_new_pending             |
      | POST   | space_api_v1_job_restart                 |
      | GET    | space_api_v1_job_list                    |
      | GET    | space_api_v1_job_get                     |
      | POST   | space_api_v1_job_delete                  |

  Scenario: From the API, as Admin, its own settings stay available
    When the API is called to get user's settings
    Then get a JSON response
    And the serialized user "Space" "Admin"
