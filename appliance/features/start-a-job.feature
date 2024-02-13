Feature: On a space instance, we can start a job from a project, User can define some variable.
  Space will clone the project from its cloning url, install all dependencies and do some other configured stuff
  in the `.paas.yaml` file, build OCI images, push them to the private OCI registry of the account, generate new
  Kubernetes manifest and apply them to the cluster

  Scenario: Execute a job from a projet and it takes too long
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And the project has a complete paas file
    And simulate a too long image building
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.newJobId          | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a timeout

  Scenario: Execute a job from a projet and it takes too long with a paas file extends
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And a project with a paas file using extends
    And simulate a too long image building
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.newJobId          | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a timeout

  Scenario: Execute a job from a projet with prefix and paas file is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.newJobId          | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed

  Scenario: Execute a job from a projet with prefix and paas file is valid with encrypted message
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.newJobId          | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed

  Scenario: Execute a job from a projet with prefix and paas file with extends is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And a project with a paas file using extends
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.newJobId          | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed

  Scenario: Execute a job from a projet with hierarchical namespace and paas file is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.newJobId          | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed

  Scenario: Execute a job from a projet with hierarchical namespace and paas file with extends is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project"
    And a project with a paas file using extends
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.newJobId          | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed

  Scenario: Execute a job from a projet with hierarchical namespace and prefix and paas file is valid
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "demo"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.newJobId          | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed

  Scenario: Execute a job from a projet with hierarchical namespace and prefix and paas file with extends is valid
    Given A Space app instance
    And a kubernetes client
    And extensions libraries provided by administrators
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-comany"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And a standard website project "my project" and a prefix "a-prefix"
    And a project with a paas file using extends
    And the platform is booted
    When the user sign in with "dupont@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.newJobId          | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed
