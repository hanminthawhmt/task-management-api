# Task Management SaaS API  
**Multi-Tenant Backend System with Stripe Subscription Billing**

---

## Overview

This project is a backend API for a SaaS-based task management platform designed for companies and teams.

The system allows organizations to register, manage team members, and subscribe to different plans (Free / Premium). Subscription billing is handled through Stripe, with a webhook-driven architecture to ensure reliable and secure payment processing.

This project focuses entirely on backend engineering, system design, and real-world SaaS architecture.

---

## Key Features

- Multi-Tenant Architecture (Company-based system)
- Role-Based Access Control (Owner, Member)
- Invitation System for team onboarding
- Email Service Integration (Mailtrap for testing emails)
- Authentication with JWT
- Stripe Subscription Integration (Laravel Cashier)
- Webhook-Based Payment Processing
- Free Plan (no payment required)
- Premium Plan with Stripe Checkout
- Subscription Status Management (pending → active → cancelled)

---

## Architecture Overview

### System Context Diagram

This diagram illustrates how the Task Management API sits at the center of the ecosystem, interacting with users and external services.

![System Context Diagram](public/system-context.png)

### Layers

- Controllers — Handle HTTP requests and responses  
- Services — Business logic (registration, onboarding)  
- Models — Database relationships  
- Webhooks — Handle Stripe events  

---

## Subscription Flow

### 1. Registration

- Free Plan → Active immediately  
- Premium Plan → Pending (requires payment)  

---

### 2. Checkout

POST /checkout → returns Stripe URL

---

### 3. Payment

Handled by Stripe Checkout page

---

### 4. Webhooks

POST /stripe/webhook

Handles:
- subscription creation
- payment success
- updates
- cancellations

---

## Flow

User → Register → Choose Plan  
→ Free = Active  
→ Premium = Pending  
→ Checkout → Payment  
→ Webhook → Active  

---

## Database

- users  
- companies  
- company_members
- roles
- projects
- project_members
- permissions
- role_permissions
- company_invitations
- project_invitations
- tasks
- activity_logs
- plans  
- subscriptions
- subscription_items

---

**Schema**
![Database Schema](public/schema.png)

## API

- POST /register  
- POST /login  
- POST /checkout  
- POST /stripe/webhook  

---

## Tech Stack

- Laravel 11  
- MySQL  
- Stripe  
- Laravel Cashier  
- JWT  

---

## Learnings

- SaaS backend architecture  
- Stripe integration  
- Webhook handling  
- Multi-tenant system design  

---
