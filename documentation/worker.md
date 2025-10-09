# Worker Documentation

## Overview

Space uses a distributed worker architecture based on Symfony Messenger to handle asynchronous job processing.
Workers are independent processes that consume messages from RabbitMQ queues and execute deployment tasks.

## Worker Types

Space includes four types of workers, each with a specific responsibility:

### 1. New Job Worker

**Purpose**: Initialize new deployment jobs

**Queue**: `new_job`

**Responsibilities**:
- Receive new job creation requests
- Validate job parameters
- Store job metadata in MongoDB
- Dispatch job to Execute Job Worker
- Publish real-time updates via Mercure (if enabled)

**Command**:
```bash
bin/console messenger:consume new_job
```

**Resource Requirements**:
- CPU: Low (1-2 cores)
- RAM: 64 - 256 MB
- Concurrency: 1-2 instances recommended

### 2. Execute Job Worker

**Purpose**: Execute deployment workflows

**Queue**: `execute_job`

**Responsibilities**:
- Clone Git repositories
- Compile `.paas.yaml` file
- Execute pre-deployment hooks (Composer, NPM, etc.)
- Build OCI images with Buildah
- Transcribe to cluster resources
- Deploy resources to Kubernetes clusters
- Monitor deployment progress
- Report history events
- Handle deployment failures

**Command**:
```bash
bin/console messenger:consume execute_job
```

**Resource Requirements**:
- CPU: High (4-8 cores)
- RAM: 4-8 GB
- Storage: 50-100 GB (for Git clones and image builds)
- Concurrency: 2-4 instances recommended
- **Requires**: Buildah installed

### 3. History Worker

**Purpose**: Persist deployment history events

**Queue**: `history_sent`

**Responsibilities**:
- Receive history events from Execute Job Worker
- Validate event data
- Store events in MongoDB
- Maintain sequential ordering

**Command**:
```bash
bin/console messenger:consume history_sent
```

**Resource Requirements**:
- CPU: Low (1-2 cores)
- RAM: 64 - 256 MB
- Concurrency: 1-2 instances recommended

### 4. Job Done Worker

**Purpose**: Finalize completed jobs

**Queue**: `job_done`

**Responsibilities**:
- Receive job completion notifications
- Update final job status
- Trigger post-deployment actions

**Command**:
```bash
bin/console messenger:consume job_done
```

**Resource Requirements**:
- CPU: Low (1-2 cores)
- RAM: 64 - 256 MB
- Concurrency: 1-2 instances recommended

## Worker Lifecycle

### Message Flow

```
1. User creates job (Web UI/API)
        ↓
2. NewJobN → new_job queue
        ↓
3. NewJob Worker processes message
        ↓
4. MessageJob → execute_job queue
        ↓
5. Execute Job Worker processes message
   ├→ HistorySent → history_sent queue (multiple)
   └→ JobDone → job_done queue
        ↓
6. History Worker persists events
        ↓
7. Job Done Worker finalizes job
```

### Worker Process Lifecycle

```
1. Worker starts
        ↓
2. Connect to RabbitMQ
        ↓
3. Subscribe to queue
        ↓
4. Wait for messages
        ↓
5. Receive message
        ↓
6. Deserialize message
        ↓
7. Execute handler
        ↓
8. Acknowledge message (success)
   OR
   Reject message (failure → retry or DLQ)
        ↓
9. Return to step 4
```

## Worker Configuration

### Environment Variables

Workers share most configuration with the web application but have specific settings:

**Required for All Workers**:
```bash
MONGODB_SERVER=mongodb://user:pass@host:27017
MONGODB_NAME=space
MESSENGER_NEW_JOB_DSN=amqp://...
MESSENGER_EXECUTE_JOB_DSN=amqp://...
MESSENGER_HISTORY_SENT_DSN=amqp://...
MESSENGER_JOB_DONE_DSN=amqp://...
```

**Execute Job Worker Specific**:
```bash
SPACE_JOB_ROOT=/var/lib/space/jobs
SPACE_WORKER_TIME_LIMIT=3600
SPACE_GIT_TIMEOUT=600
SPACE_IMG_BUILDER_CMD=buildah
SPACE_IMG_BUILDER_TIMEOUT=1800
SPACE_IMG_BUILDER_PLATFORMS=linux/amd64
SPACE_KUBERNETES_MASTER=https://...
SPACE_KUBERNETES_CREATE_TOKEN=...
```

**Health Check**:
To configure health check to kill the agent if it freeze

```bash
SPACE_PING_FILE=/var/run/space/ping
SPACE_PING_SECONDS=60
```

### Command Options

Common options for `messenger:consume`:

**--time-limit=SECONDS**
- Maximum execution time before worker restarts
- Recommended: 3600 (1 hour)
- Prevents memory leaks

```bash
bin/console messenger:consume execute_job --time-limit=3600
```

**--memory-limit=LIMIT**
- Maximum memory before worker restarts
- Example: `128M`, `512M`, `1G`

```bash
bin/console messenger:consume execute_job --memory-limit=512M
```

**--limit=COUNT**
- Process N messages then exit
- Useful for testing

```bash
bin/console messenger:consume execute_job --limit=10
```

**--failure-limit=COUNT**
- Stop after N failures
- Default: Unlimited

```bash
bin/console messenger:consume execute_job --failure-limit=3
```

## Running Workers

### Development (Docker Compose)

Workers are automatically started by Docker Compose:

```bash
./space.sh start
```

View worker logs:
```bash
docker-compose logs -f php-cli
```

### Production (systemd)

Create systemd service files for each worker type.

**Example: Execute Job Worker**

File: `/etc/systemd/system/space-worker-execute-job@.service`

```ini
[Unit]
Description=Space Execute Job Worker %i
After=network.target rabbitmq-server.service mongodb.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/opt/space/appliance
ExecStart=/usr/bin/php /opt/space/appliance/bin/console messenger:consume execute_job --time-limit=3600 --memory-limit=512M
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

# Security
PrivateTmp=true
NoNewPrivileges=true

# Resource limits
LimitNOFILE=65536
LimitNPROC=4096

[Install]
WantedBy=multi-user.target
```

**Start multiple instances**:

```bash
# Enable and start 4 instances
sudo systemctl enable space-worker-execute-job@{1..4}.service
sudo systemctl start space-worker-execute-job@{1..4}.service
```

**Manage workers**:

```bash
# Status
sudo systemctl status 'space-worker-*'

# Restart all
sudo systemctl restart 'space-worker-*'

# Stop all
sudo systemctl stop 'space-worker-*'

# View logs
sudo journalctl -u 'space-worker-*' -f
```

### Production (Supervisor)

Alternative to systemd: use Supervisor.

File: `/etc/supervisor/conf.d/space-workers.conf`

```ini
[program:space-worker-new-job]
command=/usr/bin/php /opt/space/appliance/bin/console messenger:consume new_job --time-limit=3600
directory=/opt/space/appliance
user=www-data
numprocs=2
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
startsecs=10
startretries=3
stdout_logfile=/var/log/space/worker-new-job.log
stderr_logfile=/var/log/space/worker-new-job-error.log

[program:space-worker-execute-job]
command=/usr/bin/php /opt/space/appliance/bin/console messenger:consume execute_job --time-limit=3600 --memory-limit=512M
directory=/opt/space/appliance
user=www-data
numprocs=4
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
startsecs=10
startretries=3
stdout_logfile=/var/log/space/worker-execute-job.log
stderr_logfile=/var/log/space/worker-execute-job-error.log

[program:space-worker-history]
command=/usr/bin/php /opt/space/appliance/bin/console messenger:consume history_sent --time-limit=3600
directory=/opt/space/appliance
user=www-data
numprocs=2
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
startsecs=10
startretries=3
stdout_logfile=/var/log/space/worker-history.log
stderr_logfile=/var/log/space/worker-history-error.log

[program:space-worker-job-done]
command=/usr/bin/php /opt/space/appliance/bin/console messenger:consume job_done --time-limit=3600
directory=/opt/space/appliance
user=www-data
numprocs=2
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
startsecs=10
startretries=3
stdout_logfile=/var/log/space/worker-job-done.log
stderr_logfile=/var/log/space/worker-job-done-error.log

[group:space-workers]
programs=space-worker-new-job,space-worker-execute-job,space-worker-history,space-worker-job-done
```

**Manage with supervisorctl**:

```bash
# Reload configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start all workers
sudo supervisorctl start space-workers:*

# Stop all workers
sudo supervisorctl stop space-workers:*

# Restart all workers
sudo supervisorctl restart space-workers:*

# Status
sudo supervisorctl status space-workers:*
```

## Monitoring

### Health Checks

Workers update a ping file periodically to indicate health.

**Configuration**:
```bash
SPACE_PING_FILE=/var/run/space/ping
SPACE_PING_SECONDS=60
```

**Check health**:
```bash
# File should be updated every SPACE_PING_SECONDS
stat /var/run/space/ping

# If not updated recently, worker may be stuck
find /var/run/space/ping -mmin +2
```

**Automated monitoring**:
```bash
#!/bin/bash
# check-worker-health.sh
PING_FILE=/var/run/space/ping
MAX_AGE=120  # seconds

if [ -f "$PING_FILE" ]; then
    age=$(($(date +%s) - $(stat -c %Y "$PING_FILE")))
    if [ $age -gt $MAX_AGE ]; then
        echo "CRITICAL: Worker health check file older than ${MAX_AGE}s"
        exit 2
    else
        echo "OK: Worker healthy"
        exit 0
    fi
else
    echo "CRITICAL: Worker health check file not found"
    exit 2
fi
```

### Queue Monitoring

**Check queue status**:

```bash
# Via RabbitMQ CLI
sudo rabbitmqctl list_queues name messages messages_ready messages_unacknowledged

# Via HTTP API
curl -u guest:guest http://localhost:15672/api/queues/%2F

# Via Symfony command
bin/console messenger:stats
```

**Key metrics**:
- **messages**: Total messages in queue
- **messages_ready**: Waiting to be processed
- **messages_unacknowledged**: Being processed
- **message_rate**: Messages/second

**Alerts**:
- Queue depth growing: Add more workers
- High unacknowledged count: Workers may be slow or stuck
- Low processing rate: Check worker performance



### Logging

**Log locations**:
- Systemd: `journalctl -u space-worker-*`
- Supervisor: `/var/log/space/worker-*.log`
- Application: `/opt/space/appliance/var/log/*.log`

**Log levels**:
- `ERROR`: Worker errors, job failures
- `WARNING`: Timeouts, retries
- `INFO`: Job start/completion, major steps
- `DEBUG`: Detailed execution (development only)

**Configure log level** in `.env.local`:
```bash
APP_ENV=prod  # Only ERROR and WARNING
# OR
APP_ENV=dev   # All levels including DEBUG
```

## Troubleshooting

### Common Issues

#### Workers Not Starting

**Symptoms**: Worker processes exit immediately

**Causes**:
- Missing dependencies
- Invalid configuration
- Database/RabbitMQ connection failure
- Permission issues

**Solutions**:
```bash
# Check configuration
./space.sh verify

# Test database connection
bin/console doctrine:mongodb:schema:validate

# Test RabbitMQ connection
bin/console messenger:stats

# Check permissions
ls -la /opt/space/appliance/var

# View error logs
journalctl -u space-worker-execute-job -n 50
```

#### Jobs Stuck in Queue

**Symptoms**: Jobs not processing, queue depth increasing

**Causes**:
- No workers running
- Workers crashed
- RabbitMQ issues
- Resource exhaustion

**Solutions**:
```bash
# Check worker status
systemctl status 'space-worker-*'

# Check RabbitMQ
systemctl status rabbitmq-server
rabbitmqctl cluster_status

# Check queue status
bin/console messenger:stats

# Restart workers
systemctl restart 'space-worker-*'
```

#### Jobs Failing Repeatedly

**Symptoms**: Jobs in failed status, messages in DLQ

**Causes**:
- Git repository unavailable
- Buildah errors
- Kubernetes API errors
- Invalid configuration
- Resource limits exceeded

**Solutions**:
```bash
# View failed jobs in RabbitMQ
rabbitmqctl list_queues name messages | grep failed

# Check worker logs
journalctl -u space-worker-execute-job -f

# Check application logs
tail -f /opt/space/appliance/var/log/prod.log

# Test Git access
git clone <repository-url>

# Test Kubernetes access
kubectl cluster-info

# Test Buildah
buildah --version
buildah images
```

#### High Memory Usage

**Symptoms**: Workers consuming excessive memory

**Causes**:
- Memory leaks
- Large Git repositories
- Large image builds
- Insufficient memory limit

**Solutions**:
```bash
# Set memory limit
bin/console messenger:consume execute_job --memory-limit=512M

# Set time limit (forces restart)
bin/console messenger:consume execute_job --time-limit=3600

# Monitor memory
ps aux | grep messenger:consume

# Increase system memory or add more workers
```

#### Slow Job Processing

**Symptoms**: Jobs taking too long

**Causes**:
- Slow Git clone
- Large image builds
- Slow network to Kubernetes
- Insufficient worker resources
- Hook timeouts

**Solutions**:
```bash
# Increase timeouts
SPACE_GIT_TIMEOUT=1200
SPACE_IMG_BUILDER_TIMEOUT=3600
SPACE_WORKER_TIME_LIMIT=7200

# Add more worker instances
# Increase worker resources (CPU/RAM)
# Use local Git mirror
# Optimize Dockerfiles
# Use build cache
```

### Debugging

**Enable debug logging**:

Create `.env.local`:
```bash
APP_ENV=dev
MESSENGER_TRANSPORT_DEBUG=1
```

**Run worker in foreground**:
```bash
bin/console messenger:consume execute_job -vv
```

**Process single message**:
```bash
bin/console messenger:consume execute_job --limit=1 -vvv
```

**Check message format**:
```bash
# In RabbitMQ Management UI
# Navigate to queue → Get messages → Get message
```

## Related Documentation

- [Configuration Guide](configuration.md) - Worker configuration options
- [Installation Guide](installation.md) - Worker setup instructions
- [Architecture](architecture.md) - Worker architecture overview
- [Infrastructure](infrastructure.md) - Messenger infrastructure details
