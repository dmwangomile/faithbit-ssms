# FaithBit Sales & Service Management System (SSMS)

An integrated platform that unifies omnichannel sales, after-sales services, inventory & procurement, customer relationship management (CRM), and payments & finance integration.

## Overview

This system is designed for FaithBit Company Limited to manage:
- Electronics retail & wholesale operations
- IT services/projects
- Repairs and installation work
- Multi-channel sales (POS, field sales, website, WhatsApp)
- Service management with technician scheduling
- Inventory management across multiple locations
- Customer relationship management
- Integrated payment processing (M-Pesa, Tigo Pesa, Airtel Money)

## Architecture

- **Backend**: PHP 8.2+ with Yii2 framework
- **Frontend**: Vue.js 3 with responsive design
- **Mobile**: Flutter apps for technicians and field sales
- **Database**: MySQL 8 with Redis for caching
- **Storage**: MinIO for file storage
- **Integrations**: Mobile money, SMS/WhatsApp, accounting systems

## Features

### Core Modules
1. **Product & Price Management** - Master data, pricing, inventory tracking
2. **Omnichannel Sales** - POS, B2B sales, e-commerce integration
3. **CRM & Marketing** - Customer 360, loyalty programs, campaigns
4. **Inventory & Warehousing** - Multi-location stock management
5. **Procurement** - Supplier management, purchase orders, receiving
6. **Service Management** - Work orders, warranty tracking, technician scheduling
7. **Payments & Finance** - Mobile money integration, invoicing, reconciliation
8. **Reporting & Analytics** - Real-time dashboards and reports
9. **Admin & Governance** - Role-based access, audit logs, configuration

### Key Features
- Bilingual support (English/Swahili)
- Real-time inventory tracking with IMEI/serial numbers
- Mobile money payment integration
- Offline POS capability with sync
- Service SLA tracking and alerts
- Advanced reporting and analytics
- Role-based security and access control

## Project Structure

```
├── backend/           # PHP Yii2 backend API
├── frontend/          # Vue.js web interface
├── mobile/           # Flutter mobile applications
├── docs/             # Documentation
├── scripts/          # Deployment and utility scripts
└── docker-compose.yml # Docker configuration
```

## Quick Start

1. **Backend Setup**
   ```bash
   cd backend
   composer install
   php yii migrate
   php yii serve
   ```

2. **Frontend Setup**
   ```bash
   cd frontend
   npm install
   npm run serve
   ```

3. **Database Setup**
   ```bash
   # Import database schema
   mysql -u root -p faithbit_ssms < database/schema.sql
   ```

## Development Team

- System Programmer & Systems Analyst
- Backend Engineers (PHP/Yii2)
- Frontend Engineers (Vue.js)
- Mobile Developers (Flutter)
- QA Engineers
- DevOps Engineer

## Implementation Timeline

- **Phase 1**: Core Sales & Inventory MVP (4-6 weeks)
- **Phase 2**: Service Management MVP (3-4 weeks)
- **Phase 3**: Procurement & Replenishment (2-3 weeks)
- **Phase 4**: CRM & Loyalty + Reporting v2 (2-3 weeks)
- **Phase 5**: Rollout & Change Management (2-4 weeks)

Total: 13-18 weeks to MVP

## Success Metrics

- Increase sales conversion and average order value
- Reduce stock-outs by 25-40%
- Reduce service turnaround time by 30-50%
- Improve cash collection and reduce DSO by 15-25%
- Real-time KPIs and analytics for leadership

## License

Proprietary - FaithBit Company Limited

## Contact

System Development Team
FaithBit Company Limited
"Buy Tech. Build Hope."