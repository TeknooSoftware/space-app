@web
Feature: Web interface to create new job and deploy project
  In order to deploy project
  As a user of an account
  I want to create new jobs from account's projects to deploy them

  To run a job, Space will clone the project from its cloning url, install all dependencies and do some other configured
  stuff in the `.paas.yaml` file, build OCI images, push them to the private OCI registry of the account, generate new
  Kubernetes manifest and apply them to the cluster.
  Clusters are defined from the environment passed on the job creation, from the clusters list defined in the project.

  Background:
    Given a Space app instance

  Scenario: From the UI, execute a job from an owned project, with a valid paas file, simulate a too long image building and get an error
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file
    And simulate a too long image building
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    But it has an error about a timeout

  Scenario: From the UI, execute a job from an owned project, with a valid paas file using extends, simulate a too long image building and get an error
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file using extends
    And simulate a too long image building
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    But it has an error about a timeout

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file, and display its humanized history to the user and to an administrator
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And the job history is humanized on the job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
    When the user logs out
    And the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it is redirected to the dashboard
    When it goes to the admin job page
    Then the job history is humanized on the job page

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file, encrypted messages between workers
    Given encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, defined quota, without defined resources, a valid paas file
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, defined quota, without defined resources, a valid paas file, encrypted messages between workers
    Given encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, defined quota, with partial defined resources, a valid paas file
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, defined quota, with partial defined resources, a valid paas file, encrypted messages between workers
    Given encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, defined quota, with full defined resources, a valid paas file
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, defined quota, with full defined resources, a valid paas file, encrypted messages between workers
    Given encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, defined quota, with required resources exceeded quota, a valid paas file
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a quota exceeded
    And no Kubernetes manifests have been created

  Scenario: From the UI, execute a job from an owned project, with prefix, defined quota, with required resources exceeded quota, a valid paas file, encrypted messages between workers
    Given encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And it has an error about a quota exceeded
    And no Kubernetes manifests have been created

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file with default generic values for variables and all variables are not filled
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file with default generic values for variables, encrypted messages between workers and all variables are not filled
    Given encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file with default values for variables dedicated to the cluster and all variables are not filled
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file with default values for variables dedicated to the cluster, encrypted messages between workers and all variables are not filled
    Given encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file using extends
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using extends
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, without prefix, a valid paas file, on cluster supporting hierarchical namespace
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, without prefix, a valid paas file using extends, on cluster supporting hierarchical namespace
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And a cluster supporting hierarchical namespace
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file using extends
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file, on cluster supporting hierarchical namespace
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "demo"
    And the project has a complete paas file
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file using extends, on cluster supporting hierarchical namespace
    Given a kubernetes client
    And extensions libraries provided by administrators
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using extends
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file, using conditions
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using conditions
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
      | new_job.variables.2.name  | ENV                     |
      | new_job.variables.2.value | prod                    |
      | new_job.variables.3.name  | PHP_VERSION             |
      | new_job.variables.3.value | 7.4                     |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file, using conditions and encrypted messages between workers
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using conditions
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
      | new_job.variables.2.name  | ENV                     |
      | new_job.variables.2.value | prod                    |
      | new_job.variables.3.name  | PHP_VERSION             |
      | new_job.variables.3.value | 7.4                     |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file, jobs
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with jobs
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file, jobs and encrypted messages between workers
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with jobs
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with a valid paas file, using conditions
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file using conditions
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
      | new_job.variables.2.name  | ENV                     |
      | new_job.variables.2.value | prod                    |
      | new_job.variables.3.name  | PHP_VERSION             |
      | new_job.variables.3.value | 7.4                     |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with a valid paas file, using conditions and encrypted messages between workers
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file using conditions
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
      | new_job.variables.2.name  | ENV                     |
      | new_job.variables.2.value | prod                    |
      | new_job.variables.3.name  | PHP_VERSION             |
      | new_job.variables.3.value | 7.4                     |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with a valid paas file, jobs
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file with jobs
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with a valid paas file, jobs and encrypted messages between workers
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file with jobs
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file, using expose shortcuts (v1.2)
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using expose shortcuts
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with prefix, a valid paas file, using expose shortcuts (v1.2) and encrypted messages between workers
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file using expose shortcuts
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with a valid paas file, using expose shortcuts (v1.2)
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file using expose shortcuts
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the UI, execute a job from an owned project, with a valid paas file, using expose shortcuts (v1.2) and encrypted messages between workers
    Given a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a memory document database
    And an account for "My Company" with the account namespace "my-company"
    And a user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication is enabled for the last user
    And a standard project "my project"
    And the project has a complete paas file using expose shortcuts
    And the platform is booted
    And the user is signed in with "dupont@teknoo.space" and the password "Test2@Test"
    And It goes to projects list page
    And it goes to project page of "my project"
    When it runs a job
    And it submits the form:
      | field                     | value                   |
      | new_job._token            | <auto>                  |
      | new_job.projectId         | <auto>                  |
      | new_job.taskId            | <auto>                  |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then it obtains a deployment page
    And Space executes the job
    And it is forwared to job page
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"
