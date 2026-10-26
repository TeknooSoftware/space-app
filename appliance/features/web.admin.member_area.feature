@web @admin
Feature: Web interface of the members of an account, refused to the administrators of Space
  In order to keep the projects, the jobs and the settings of an account in the hands of its users
  As an administrator of Space
  I can not use the pages of the members of an account

  An administrator of Space is never a user of an account. It supervises the accounts from the administration, but it
  can not open the pages of the members (projects, jobs, settings, variables and clusters of an account). Its own
  settings (password, 2FA, API keys, JWT) and the dashboard stay available.

  Background:
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And a standard project "my project" and a prefix "a-prefix"
    And "1" jobs for the project

  Scenario Outline: From the UI, as Admin, the pages of the members of an account are refused
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    And it is redirected to the dashboard
    When it opens the page of the route "<route>"
    Then the user must have a 403 error

    Examples:
      | route                                  |
      | space_project_list                     |
      | space_project_edit                     |
      | space_project_edit_variables           |
      | space_project_edit_refresh_credentials |
      | space_project_new                      |
      | space_project_delete                   |
      | space_job_new                          |
      | space_job_restart                      |
      | space_job_new_pending                  |
      | space_job_get                          |
      | space_job_list                         |
      | space_job_delete                       |
      | space_account_settings                 |
      | space_account_status                   |
      | space_account_edit_variables           |
      | space_account_clusters_list            |
      | space_account_clusters_edit            |
      | space_account_clusters_new             |
      | space_account_clusters_delete          |

  Scenario Outline: From the UI, as Admin, its own settings and the dashboard stay available
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    And it is redirected to the dashboard
    When it opens the page of the route "<route>"
    Then get a valid web page

    Examples:
      | route                          |
      | space_dashboard                |
      | space_my_settings              |
      | _teknoo_common_jwt_create      |
      | _teknoo_common_api_keys_manage |

  Scenario: From the UI, a user of an account with its 2FA in progress is still sent to the TOTP code page
    Given the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    And it must be redirected to the TOTP code page
    When it opens the page of the route "space_project_list"
    Then it must be redirected to the TOTP code page

  Scenario Outline: From the UI, as Admin, the pages of the anonymous visitors are refused once signed in
    Given an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    And it is redirected to the dashboard
    When it opens the page of the route "<route>"
    Then the user must have a 403 error

    Examples:
      | route                        |
      | space_subscription           |
      | _teknoo_common_user_recovery |
