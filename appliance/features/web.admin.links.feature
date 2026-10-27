@web @admin
Feature: Web admin interface without links to the forbidden operations
  In order to use only the operations allowed to the administrators of Space
  As an administrator of Space
  I want pages with no link or button to an operation that gives me a 403

  Background:
    Given a Space app instance
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And "1" jobs for the project
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    And it is redirected to the dashboard

  Scenario Outline: From the UI, as Admin, a page has no link to a forbidden operation
    When it opens the admin page "<page>" of the last account
    Then get a valid web page
    And the page has no link to the route "<route>"

    Examples:
      | page                            | route                                     |
      | space_dashboard                 | _teknoo_common_admin_media_list           |
      | _teknoo_paas_admin_account_list | _teknoo_paas_admin_project_new            |
      | _teknoo_paas_admin_account_edit | space_admin_account_edit_variables        |
      | _teknoo_paas_admin_account_edit | space_admin_account_clusters_list         |
      | _teknoo_paas_admin_account_edit | space_admin_account_environment_reinstall |
      | _teknoo_paas_admin_account_edit | _teknoo_paas_admin_project_new            |
      | _teknoo_paas_admin_project_list | _teknoo_paas_admin_project_edit           |
      | _teknoo_paas_admin_project_list | _teknoo_paas_admin_project_delete         |
      | _teknoo_paas_admin_project_list | space_admin_job_new                       |
      | _teknoo_paas_admin_job_list     | _teknoo_paas_admin_project_edit           |
      | space_admin_job_get             | _teknoo_paas_admin_project_edit           |
      | space_admin_job_get             | space_admin_job_restart                   |
