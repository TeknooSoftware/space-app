@web @admin
Feature: Web admin pages rendered without the removed operations
  In order to use the operations allowed to the administrators of Space
  As an administrator of Space
  I want every page of the administration to be displayed, without a link to a page which does not exist

  A link to a route which does not exist stops the rendering of its page.

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

  Scenario Outline: From the UI, as Admin, a page of the administration is displayed
    When it opens the admin page "<page>" of the last account
    Then get a valid web page

    Examples:
      | page                            |
      | space_dashboard                 |
      | _teknoo_paas_admin_account_list |
      | _teknoo_paas_admin_account_edit |
      | _teknoo_paas_admin_project_list |
      | _teknoo_paas_admin_job_list     |
      | space_admin_job_get             |
