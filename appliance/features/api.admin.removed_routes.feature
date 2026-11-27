@api @admin
Feature: API admin endpoints removed with the restriction of the rights of the administrators
  In order to keep the administration to the operations it is allowed to do
  As an administrator of Space
  I want no endpoint for the operations an administrator can not do

  An administrator of Space can not change the variables, the clusters or the environments of an account, open, create
  or delete a project, nor start a job. These endpoints do not exist. The environments of an account stay readable.

  Background:
    Given a Space app instance
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And the platform is booted
    And the user is authenticated on the API with "admin@teknoo.space" and the password "Test2@Test"

  # The path of the creation of a job is also the one of the reading of a job named "new", only in GET
  Scenario Outline: From the API, as Admin, a removed endpoint does not exist
    When the API is called with the method "<method>" on the path "<path>"
    Then the user must have a <status> error
    And no route handles the request

    Examples:
      | method | path                                                               | status |
      | GET    | /api/v1/admin/account/anId/variables                               | 404    |
      | POST   | /api/v1/admin/account/anId/environment/prod/aCluster/reinstall     | 404    |
      | POST   | /api/v1/admin/account/anAccount/cluster/new                        | 404    |
      | GET    | /api/v1/admin/account/anAccount/cluster/anId                       | 404    |
      | POST   | /api/v1/admin/account/anAccount/cluster/anId/delete                | 404    |
      | POST   | /api/v1/admin/account/anAccount/project/aProject/job/new           | 405    |
      | GET    | /api/v1/admin/account/anAccount/project/aProject/job/pending/aTask | 404    |
      | POST   | /api/v1/admin/account/anAccount/project/new                        | 404    |
      | GET    | /api/v1/admin/account/anAccount/project/anId                       | 404    |
      | GET    | /api/v1/admin/account/anAccount/project/anId/variables             | 404    |
      | POST   | /api/v1/admin/account/anAccount/project/anId/refresh-credentials   | 404    |
      | POST   | /api/v1/admin/account/anAccount/project/anId/delete                | 404    |
      | POST   | /api/v1/admin/account/anId/environments                            | 405    |
      | PUT    | /api/v1/admin/account/anId/environments                            | 405    |
