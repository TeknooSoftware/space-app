# System Requirements

## Overview

Space is a Platform as a Service application with specific hardware, software, and infrastructure requirements. 
This document outlines all necessary prerequisites for running Space in development and production environments.

## Minimum Hardware Requirements

### Web Server / Application Server

**Development Environment:**
- CPU: 2 cores
- RAM: 8 GB
- Storage: 10 GB available space
- Network: Standard network connectivity

**Production Environment (Small Scale):**
- CPU: 4 cores
- RAM: 8 GB
- Storage: 50 GB available space (SSD recommended)
- Network: High-speed network connectivity (1 Gbps+)

**Production Environment (Medium to Large Scale):**
- CPU: 8+ cores per web server instance
- RAM: 16+ GB per instance
- Storage: 100+ GB SSD
- Network: High-speed network with redundancy

### Worker Servers

**Per Worker Instance:**
- CPU: 1 cores minimum (2+ recommended for build workers)
- RAM: 2 GB minimum (4+ GB recommended for build workers)
- Storage: 1+ GB (100+ GB for build workers with Buildah)
- Network: High-speed network connectivity

**Recommended Worker Distribution:**
- 1-2 instances for New Job Worker
- 2-4 instances for Execute Job Worker (build-intensive)
- 1-2 instances for History Worker
- 1-2 instances for Job Done Worker

### Database Server (MongoDB)

**Development:**
- CPU: 2 cores
- RAM: 4 GB
- Storage: 20 GB

**Production:**
- CPU: 4+ cores
- RAM: 16+ GB (32+ GB recommended)
- Storage: 200+ GB SSD with IOPS optimization
- Backup storage: Additional capacity for backups

### Message Broker (RabbitMQ)

**Development:**
- CPU: 1 core
- RAM: 2 GB
- Storage: 10 GB

**Production:**
- CPU: 2-4 cores
- RAM: 4-8 GB
- Storage: 50+ GB
- Clustering: 3+ nodes recommended for high availability

### Kubernetes Cluster

**Minimum (Development/Testing):**
- 1 control plane node
- 2+ worker nodes
- Total: 8 GB RAM, 4 cores per node

**Production:**
- 3+ control plane nodes (HA setup)
- 5+ worker nodes (depending on workload)
- Storage provisioner (NFS, Ceph, cloud provider)
- LoadBalancer support
- Ingress controller (nginx, traefik, etc.)
- cert-manager for TLS certificates

## Software Requirements

### Required Components

#### PHP

**Version:** PHP 8.4 or higher

**Required Extensions:**
- `ext-bcmath`: Arbitrary precision mathematics
- `ext-ctype`: Character type checking
- `ext-curl`: HTTP client functionality
- `ext-dom`: DOM manipulation
- `ext-fileinfo`: File information
- `ext-filter`: Data filtering
- `ext-iconv`: Character encoding conversion
- `ext-intl`: Internationalization
- `ext-json`: JSON encoding/decoding
- `ext-libxml`: XML library
- `ext-mbstring`: Multibyte string handling
- `ext-mongodb`: MongoDB driver
- `ext-openssl`: OpenSSL cryptography
- `ext-pcre`: Regular expressions
- `ext-pdo`: Database abstraction
- `ext-simplexml`: Simple XML parsing
- `ext-sodium`: Modern cryptography
- `ext-xml`: XML parsing

**Recommended PHP Configuration:**

```ini
; Performance
memory_limit = 512M
max_execution_time = 300
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0  ; Production only

; Security
expose_php = Off
display_errors = Off  ; Production only
log_errors = On

; Session
session.cookie_secure = 1  ; HTTPS only
session.cookie_httponly = 1
session.cookie_samesite = Lax

; File uploads (for deployment artifacts)
upload_max_filesize = 100M
post_max_size = 100M
```

#### Web Server

**Supported:**
- **Apache HTTP Server 2.4+** with mod_rewrite
  - Or with PHP-FPM via proxy
- **Nginx 1.18+** with PHP-FPM
- **Caddy 2.0+** (alternative)

**Required Modules (Apache):**
- mod_rewrite
- mod_headers
- mod_deflate (compression)
- mod_ssl (HTTPS)

**Required Configuration (Nginx):**
- PHP-FPM with FastCGI
- Proper location blocks for Symfony
- SSL/TLS support

#### MongoDB

**Version:** MongoDB 5= or higher (7 recommended)

**Required Features:**
- Replica set support (production)
- WiredTiger storage engine
- Index support
- Authentication enabled

**Recommended Configuration:**
- Replica set with 3+ members (production)
- Oplog enabled
- Journaling enabled
- Authentication: SCRAM-SHA-256
- TLS/SSL connections (production)

#### RabbitMQ

**Version:** RabbitMQ 3.9+ (3.11+ recommended)

**Required Plugins:**
- rabbitmq_management (web UI)
- rabbitmq_shovel (optional, for message forwarding)
- rabbitmq_federation (optional, for distributed setups)

**Required Configuration:**
- Message persistence enabled
- Durable queues
- Acknowledgments configured
- Dead letter exchanges

#### Buildah

**Version:** Buildah 1.28+ (latest stable recommended)

**Purpose:** OCI/Docker image building

**Requirements:**
- Linux system with kernel 4.18+
- root or rootless mode configured
- Storage driver: overlay2 or vfs
- Container runtime: Optional (can work standalone)

**Note:** Buildah is only required on worker servers that execute deployment jobs (Execute Job Worker).

#### Mercure (Optional but Recommended)

**Version:** Mercure 0.20+ 

**Purpose:** Real-time Server-Sent Events (SSE) for live updates

**Requirements:**
- HTTP/2 support
- JWT authentication
- CORS configuration

#### Kubernetes

**Version:** Kubernetes 1.30 or higher

**Required Components:**
- Kubernetes API server
- kubectl access
- Service account with appropriate permissions
- Ingress controller
- Storage provisioner

**Recommended Components:**
- cert-manager for TLS certificates
- Kubernetes Dashboard
- Metrics server
- Prometheus/Grafana (monitoring)
- Hierarchical Namespace Controller (HNC) - optional

**Required API Resources:**
- Namespaces
- Pods
- Services
- Deployments
- StatefulSets
- ConfigMaps
- Secrets
- Ingresses
- PersistentVolumeClaims
- ResourceQuotas
- Roles / RoleBindings
- ServiceAccounts

#### Composer

**Version:** Composer 2.8+

**Purpose:** PHP dependency management


### Optional Components

#### Redis

**Purpose:** Session storage and caching (alternative to MongoDB sessions)

**Version:** Redis 6.0+

#### SMTP Server

**Purpose:** Email notifications (contact forms, password resets)

**Options:**
- Local mail server (Postfix, Exim)
- External SMTP service (SendGrid, Mailgun, AWS SES)

#### Monitoring Tools

- **Prometheus**: Metrics collection
- **Grafana**: Metrics visualization
- **ELK Stack**: Log aggregation and analysis
- **Sentry**: Error tracking

## Network Requirements

### Ports

**Web Server:**
- 80 (HTTP) - Redirect to HTTPS
- 443 (HTTPS) - Primary web traffic

**MongoDB:**
- 27017 (default) - Database connections
- Internal network only (firewall protected)

**RabbitMQ:**
- 5672 (AMQP) - Message broker
- 15672 (HTTP) - Management UI
- Internal network only

**Mercure:**
- 3000 (default) - SSE endpoint
- Accessible by web clients

**Kubernetes API:**
- 6443 (default) - API server
- Accessible by Space workers

### Firewall Rules

**Inbound:**
- Allow 80/443 from internet (web traffic)
- Allow 22 from specific IPs (SSH management)
- Allow Kubernetes API access from worker nodes

**Outbound:**
- Allow HTTPS to Git repositories
- Allow HTTPS to Kubernetes API
- Allow SMTP (if using external mail)
- Allow Docker/OCI registry access

**Internal:**
- Web servers → MongoDB
- Web servers → RabbitMQ
- Web servers → Mercure
- Workers → MongoDB
- Workers → RabbitMQ
- Workers → Kubernetes API
- Workers → Git repositories
- Workers → OCI registries

### DNS Requirements

- Primary domain for Space application
- Wildcard DNS for account subdomains (optional)
- DNS for Kubernetes ingresses
- Internal DNS for service discovery (Kubernetes)

## SSL/TLS Requirements

### Certificates

**Web Server:**
- Valid SSL/TLS certificate (Let's Encrypt, commercial CA)
- Support for TLS 1.2+ (TLS 1.3 recommended)
- Strong cipher suites

**Kubernetes Ingresses:**
- cert-manager for automatic certificate management
- Let's Encrypt or other ACME provider
- Or manual certificate management

**Kubernetes API:**
- Self-signed or CA-signed certificate
- Certificate authority (CA) certificate available

**Internal Services:**
- TLS for MongoDB connections (production)
- TLS for RabbitMQ connections (production)

## Storage Requirements

### Web Server Storage

- Application code: ~500 MB
- Symfony cache: 100-500 MB
- Logs: Variable (log rotation recommended)
- Session storage: Variable (if file-based)

## Development Requirements

### Additional Tools

- **Make**: Build automation (`make`)
- **Docker/Docker Compose**: Local development environment
