Feature: API admin endpoints to create new job and deploy project
  In order to deploy project
  As an administrator of Space
  I want to create new jobs from account's projets to deploy them

  To run a job, Space will clone the project from its cloning url, install all dependencies and do some other configured
  stuff in the `.paas.yaml` file, build OCI images, push them to the private OCI registry of the account, generate new
  Kubernetes manifest and apply them to the cluster.
  Clusters are defined from the environment passed on the job creation, from the clusters list defined in the project.

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file, via a request with a
  form url encoded body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin:
      | field                     | value                   |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file, encrypted messages
  between workers, via a request with a form url encoded body
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin:
      | field                     | value                   |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, re-execute a job from a project, with prefix, a valid paas file, encrypted messages
  between workers, via a request with a form url encoded body
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And "1" jobs for the project
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to restart a the job as admin:
      | field                     | value                   |
      | new_job.envName           | prod                    |
      | new_job.variables.0.name  | FOO                     |
      | new_job.variables.0.value | BAR                     |
      | new_job.variables.1.name  | SERVER_SCRIPT           |
      | new_job.variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file, via a request with
  a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file, encrypted
  messages between workers, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, defined quota, without defined resources,
  a valid paas file, via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, defined quota, without defined resources,
  a valid paas file, encrypted messages between workers, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file without resources
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, defined quota, with partial defined
  resources, a valid paas file, via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, defined quota, with partial defined
  resources, a valid paas file, encrypted messages between workers, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with partial resources
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, defined quota, with full defined
  resources, a valid paas file, encrypted messages between workers, via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, defined quota, with fully defined
  resources, a valid paas file, encrypted messages between workers, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with resources
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, defined quota, with required resources
  exceeded quota, a valid paas file, via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created

  Scenario: From the API, as Admin, execute a job from a project, with prefix, defined quota, with required resources
  exceeded quota, a valid paas file, encrypted messages between workers, via a request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And quotas defined for this account
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with limited quota
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And it has an error about a quota exceeded
    And no Kubernetes manifests must not be created

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file with default generic
  values for variables and all variables are not filled via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file with default generic
  values for variables, encrypted messages between workers and all variables are not filled via a request with a
  json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file with default values for
  variables dedicated to the cluster and all variables are not filled via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file with default values for
  variables dedicated to the cluster, encrypted messages between workers and all variables are not filled via a
  request with a json body
    Given A Space app instance
    And encryption capacities between servers and agents
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And the project has a complete paas file with defaults for the cluster
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file using extends, via a
  request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And a project with a paas file using extends
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, without prefix, a valid paas file, on cluster
  supporing hierarchical namespace, via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, without prefix, a valid paas file using extends, on
  cluster supporting hierarchical namespace, via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And extensions libraries provided by administrators
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project"
    And a project with a paas file using extends
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file, on cluster supporting
  hierarchical namespace, via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "demo"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file using extends, on
  cluster supporting hierarchical namespace, via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And extensions libraries provided by administrators
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And a cluster supporting hierarchical namespace
    And A memory document database
    And an account for "My Company" with the account namespace "my-company"
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix"
    And a project with a paas file using extends
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Demo Kube Cluster"

  Scenario: From the API, as Admin, execute a job from a project, with prefix, a valid paas file, on an account cluster
  via a request with a json body
    Given A Space app instance
    And a kubernetes client
    And a job workspace agent
    And a git cloning agent
    And a composer hook as hook builder
    And an OCI builder
    And A memory document database
    And an admin, called "Space" "Admin" with the "admin@teknoo.space" with the password "Test2@Test"
    And the 2FA authentication enable for last user
    And an account for "My Company" with the account namespace "my-company"
    And an account clusters "Cluster Company" and a slug "my-company-cluster"
    And an account environment on "Cluster Company" for the environment "prod"
    And an user, called "Dupont" "Jean" with the "dupont@teknoo.space" with the password "Test2@Test"
    And a standard project "my project" and a prefix "a-prefix" on "Cluster Company" for "prod"
    And the project has a complete paas file
    And the platform is booted
    When the user sign in with "admin@teknoo.space" and the password "Test2@Test"
    Then it must redirected to the TOTP code page
    When the user enter a valid TOTP code
    And get a JWT token for the user
    And the user logs out
    When the API is called to create a new job as admin with a json body:
      | field             | value                   |
      | envName           | prod                    |
      | variables.0.name  | FOO                     |
      | variables.0.value | BAR                     |
      | variables.1.name  | SERVER_SCRIPT           |
      | variables.1.value | /opt/app/src/server.php |
    Then get a JSON reponse
    And a pending job id with admin route
    When the API is called to pending job status api
    Then get a JSON reponse
    And a pending job status without a job id
    When Space executes the job
    And the API is called to get the last generated job
    Then get a JSON reponse
    And the serialized job
    And job must be successful finished
    And some Kubernetes manifests have been created and executed on "Cluster Company"
