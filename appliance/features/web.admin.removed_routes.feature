@web @admin
Feature: Web admin pages removed with the restriction of the rights of the administrators
  In order to keep the administration to the operations it is allowed to do
  As an administrator of Space
  I want no page for the operations an administrator can not do

  An administrator of Space can not change the variables, the clusters or the environments of an account, open, create
  or delete a project, start or restart a job, nor manage the media. These pages do not exist.

  Background:
    Given a Space app instance
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is signed in with "admin@teknoo.space" and the password "Test2@Test"

  Scenario Outline: From the UI, as Admin, a removed page does not exist
    When it calls the path "<path>" with the method "GET"
    Then the user must have a 404 error
    And no route handles the request

    Examples:
      | path                                                    |
      | /admin/media                                            |
      | /admin/media/new                                        |
      | /admin/media/delete/anId                                |
      | /admin/project/anId/edit                                |
      | /admin/account/anAccount/project/new                    |
      | /admin/project/anId/delete                              |
      | /admin/account/anId/variables                           |
      | /admin/account/anId/environment/prod/aCluster/reinstall |
      | /admin/account/anAccount/clusters                       |
      | /admin/account/anAccount/cluster/anId/edit              |
      | /admin/account/anAccount/cluster/new                    |
      | /admin/account/anAccount/cluster/anId/delete            |
      | /admin/project/aProject/job/new                         |
      | /admin/project/aProject/job/aJob/restart                |
