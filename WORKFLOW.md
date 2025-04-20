# Doctrine IP Bundle Workflow

This document describes the workflow and data flow of the Doctrine IP Bundle.

## IP Tracking Workflow

```mermaid
flowchart TD
    A[Entity Operation] -->|Triggers| B[Doctrine Lifecycle Event];
    B -->|Intercepted by| C[IpTrackListener];
    C -->|Checks| D{Has Client IP?};
    D -->|No| E[Skip Processing];
    D -->|Yes| F{Entity Creation?};

    F -->|Yes| G[Find Properties with CreateIpColumn];
    F -->|No| H[Find Properties with UpdateIpColumn];

    G -->|For Each Property| I{Property Empty?};
    H -->|For Each Property| I;

    I -->|No| J[Skip Property];
    I -->|Yes| K[Set Client IP to Property];

    L[HTTP Request] -->|Intercepted by| M[KernelRequest Event];
    M -->|Captured by| N[IpTrackListener.onKernelRequest];
    N -->|Stores| O[Client IP in Listener];
    O -->|Available for| C;
```

## Component Interaction

```mermaid
sequenceDiagram
    participant Client as Client Request
    participant Kernel as Symfony Kernel
    participant Listener as IpTrackListener
    participant Doctrine as Doctrine ORM
    participant Entity as Entity Object

    Client->>Kernel: HTTP Request
    Kernel->>Listener: kernel.request event
    Listener->>Listener: setClientIp(request.getClientIp())

    Note over Client,Entity: Later during request processing

    Client->>Doctrine: persist/update entity
    Doctrine->>Listener: prePersist/preUpdate event
    Listener->>Listener: getClientIp()
    Listener->>Entity: Check for IP attributes

    alt Has CreateIpColumn/UpdateIpColumn attributes
        Listener->>Entity: Set client IP to property
    end

    Doctrine->>Client: Return response
    Kernel->>Listener: reset() (clear client IP)
```

## Configuration Flow

```mermaid
flowchart LR
    A[Bundle Registration] --> B[DoctrineIpExtension];
    B --> C[Load services.yaml];
    C --> D[Register IpTrackListener];
    D --> E[Configure Event Subscribers];
    E --> F[Ready for Use];

    G[Doctrine Entity] -->|Uses| H[CreateIpColumn Attribute];
    G -->|Uses| I[UpdateIpColumn Attribute];
    H --> J{Entity Created};
    I --> K{Entity Updated};
    J -->|Yes| L[Set Client IP];
    K -->|Yes| L;
```

This workflow documentation illustrates how the Doctrine IP Bundle intercepts HTTP requests to capture client IPs and automatically applies them to entity properties marked with the appropriate attributes during entity creation and updates.
