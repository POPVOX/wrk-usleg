# LegiDash

**The command center for legislative offices.**

A comprehensive operating system for modern elected officials' offices. Built with Laravel 12, Livewire 3, and Tailwind CSS.

## Overview

LegiDash streamlines legislative tracking, stakeholder relationship management, and team collaboration for elected offices. Supports **federal, state, and local** elected offices.

> **Note**: LegiDash is focused on **legislative operations and policy work**—not constituent casework or service requests. It helps offices track issues, prepare for meetings, and understand the elected official's policy landscape.

> **Beta Status**: Currently in private beta. [Request access](/) on the homepage.

## Features

### Core Modules

#### 🗂️ Issues (Legislative Tracking)
Track legislation, policy priorities, and office initiatives with a hierarchical issue structure.

- **Issue Types**: Bill tracking, policy positions, policy research, internal projects
- **Priority Levels**: Member Priority, Office Priority, Standard
- **Legislative Fields**: Bill numbers, Congress session, legislative status, chamber tracking
- **Workstreams**: Break issues into manageable work streams
- **Documents**: Attach and organize documents per issue
- **AI Collaborator**: Get AI assistance for drafting, research, and analysis

#### 👥 People & Organizations (Relationship Management)
Manage relationships with stakeholders, advocates, lobbyists, agency contacts, and partners.

- **Contact Management**: Full contact profiles with interaction history
- **Organization Tracking**: Track companies, advocacy groups, government agencies, think tanks
- **Relationship Linking**: Connect people to organizations and issues
- **Meeting History**: See all interactions with each contact

> **Note**: This is stakeholder/relationship management for policy work, not a constituent services or casework system.

#### 📅 Meetings
Capture, prepare for, and follow up on meetings.

- **Meeting Types**: Constituent, stakeholder, committee hearing, floor vote, internal, media, district event
- **Preparation Tools**: Talking points, briefing materials, participant info
- **Action Items**: Track follow-ups and assignments
- **Meeting Notes**: Capture notes with AI transcription support
- **Calendar Integration**: Google Calendar sync

#### 📰 Media & Press
Track media coverage and manage communications.

- **Press Clips**: Monitor coverage with sentiment analysis (Positive/Neutral/Negative)
- **Media Outlets**: Track relationships with journalists and outlets
- **Pitch Tracking**: Manage media pitches and inquiries
- **Coverage Analytics**: See media trends over time

#### 🧠 Knowledge Hub
AI-powered search and insights across all office data.

- **Semantic Search**: Find information by meaning, not just keywords
- **Cross-Module Search**: Search across meetings, issues, people, and documents
- **Recent Insights**: AI-surfaced decisions and important updates
- **Quick Access**: Fast links to all modules

#### 📚 Knowledge Base
Centralized document repository with search.

- **Document Library**: Store and organize all office documents
- **Issue Filtering**: View documents by associated issue
- **Full-Text Search**: Find content within documents

#### 👨‍👩‍👧‍👦 Team Hub
Coordinate staff activities and workloads.

- **Team Directory**: View all team members and their roles
- **Workload Visibility**: See who's working on what
- **Profile Pages**: Individual staff profiles with assignments

---

### 🏛️ Member Hub

A comprehensive dashboard providing immediate context about the elected official.

#### Features:
- **Member Profile**: Photo, party, district, committee assignments, bio
- **Real-Time Alerts**: Urgent meetings, negative press, overdue actions
- **AI Suggestions**: Proactive recommendations based on office activity
- **Member Location**: Track where the official is (DC/Capitol, District, Traveling) with timezone
- **Today's Schedule**: Member-required meetings for the day
- **Stats Dashboard**: Active issues, meetings, pending actions, priorities
- **Priority Issues**: Quick view of Member and Office priorities
- **Policy Feedback Summary**: Aggregated themes from constituent communications (not case management)
- **Communications**: Recent public statements and media pickups
- **Media Coverage**: 7-day sentiment breakdown
- **District Events**: Upcoming district engagements
- **Policy Positions**: Key policy areas with evolution tracking

---

### 📋 Member Priorities & Interests

A comprehensive questionnaire system to capture what matters most to the elected official. This information personalizes the Member Hub, informs AI suggestions, and helps new staff understand the official's priorities.

**Route**: `/setup/priorities`

#### Multi-Level Support

The questionnaire adapts based on government level (federal, state, or local):

| Aspect | Federal | State Legislature | Local Government |
|--------|---------|-------------------|------------------|
| Policy Areas | 27 federal-focused options | 23 state-focused options | 21 local-focused options |
| Philosophy Options | Partisan/ideological | Partisan/ideological | Non-partisan/governance style |
| Section 2 | Standard | Skippable | Skippable (often non-partisan) |
| New Fields | - | Session type, other occupation, state-federal issues | Role type, governance structure, boards/commissions |

#### 6-Section Questionnaire:

##### 1. Policy Priorities
- **Top Policy Areas**: Ranked list of policy focus areas (level-specific options)
- **Signature Issues**: What the official wants to be known for
- **Emerging Interests**: New areas of developing focus

##### 2. Political Positioning (Skippable)
- **Governing Philosophy**: Level-specific options
  - Federal/State: Progressive, bipartisan, conservative, pragmatic, etc.
  - Local: Pro-growth, managed growth, fiscally conservative, collaborative, etc.
- **Philosophy Description**: In their own words
- **"Red Line" Issues**: Non-negotiable positions
- **Bipartisan/Coalition Openings**: Areas open to collaboration

##### 3. District/Community Focus
- **Key Groups**: Important constituency/community groups
- **Economic Priorities**: Key industries, job sectors, development focuses
- **Top Concerns**: Issues frequently raised by constituents

##### 4. Personal Background
- **Professional Background**: Career experience shaping perspectives
- **Other Occupation**: (State only) Primary job outside legislature
- **Formative Experiences**: Life events that shaped views
- **Personal Connections**: Personal ties to policy issues

##### 5. Communication Style
- **Preferred Tone**: Formal, conversational, passionate, measured, etc.
- **Key Phrases**: Language the official likes to use
- **Topics to Emphasize**: Subjects they love to discuss
- **Topics to Avoid**: Sensitive areas requiring care

##### 6. Goals & AI Settings
- **Session Type**: (State only) Full-time, part-time, biennial
- **State-Federal Issues**: (State only) Federal coordination areas
- **Role Type**: (Local only) Council ward, at-large, mayor, etc.
- **Governance Structure**: (Local only) Council-manager, strong mayor, etc.
- **Boards & Commissions**: (Local only) Committees served on
- **Term Goals**: Priorities for the current term
- **Long-Term Vision**: Career and impact aspirations
- **Legacy Items**: What they want to be remembered for
- **AI Context**: Custom notes for AI prompt personalization
- **Toggle**: Enable/disable use in AI prompts

#### How It's Used:
```php
// In AI services, get personalized context:
$profile = MemberProfile::current();
$context = $profile->getAiContextSummary();

// Returns formatted context like:
// "Top policy priorities: Veterans Affairs, Defense, Immigration
//  Governing approach: bipartisan
//  Signature issues: Veterans mental health, Port infrastructure
//  Non-negotiable positions: No cuts to veterans benefits
//  Preferred communication tone: conversational"
```

---

### ⚙️ Setup Wizard

Guided onboarding for configuring the office. Supports multiple government levels:

**Route**: `/setup`

#### Government Levels:
- **Federal**: U.S. Congress (House & Senate)
  - Automatic member lookup via Congress.gov API
  - Import biography and legislative record
  - District geography auto-detection

- **State**: State Legislatures
  - Manual entry with legislative activity URL option
  - Supports scraping state legislature sponsor pages
  - Example: [Tennessee General Assembly](https://wapp.capitol.tn.gov/apps/sponsorlist/)

- **Local**: City/County Officials
  - Manual entry with optional council portal URL
  - Social media activity tracking

#### Setup Steps:
1. **Basic Info**: Name, title, party, state, district, government level
2. **Verify Information**: Review and add office details
3. **News Sources**: Select relevant media outlets to monitor
4. **Import/Configure**: 
   - Federal: Import from Congress.gov
   - State/Local: Configure legislative activity URL
5. **Review & Launch**: Summary and next steps

#### Social Media Support:
- Twitter/X
- Facebook
- Instagram
- YouTube
- LinkedIn
- Bluesky
- TikTok

---

### 📊 Dashboard System

Two dashboard options based on staff role:

1. **Personal Dashboard** (`/dashboard/personal`)
   - Focus on individual assignments and tasks
   - My upcoming meetings
   - My action items
   - Issues I'm assigned to

2. **Office Overview** (`/dashboard/overview`)
   - Bird's-eye view of entire office operations
   - All active issues
   - Office-wide meeting calendar
   - Team workload distribution
   - Key metrics

*Dashboard routing is automatic based on staff title/role, with manual toggle available.*

---

### 🛡️ Management & Admin

#### Management Section (for CoS/LD):

**Team Overview** (`/management/team`)

A dashboard for Chiefs of Staff and Legislative Directors to monitor team activity:

- **Staff Workload Grid**: See each staffer's current issue assignments at a glance
- **Issue Assignment Matrix**: Which issues are assigned to whom, with status indicators
- **Activity Summary**: Recent activity by team member (meetings added, issues updated)
- **Assign Issues**: Quickly assign or reassign issues to team members
- **Workload Balancing**: Identify overloaded or underutilized staff
- **Coverage Gaps**: See issues without clear ownership

*Access requires Management role (CoS, LD, Deputy Chief, Office Manager).*

#### Admin Section:
- **How AI Works**: Transparency page explaining AI features
  - Model information and data handling
  - Prompt viewing and customization
  - Enable/disable AI features
- **Billing & Plan**: Beta pricing info, seat management
- **Integrations**: Third-party service connections
- **Office Settings**: Configure office preferences
- **Setup Wizard**: Re-run initial setup
- **Member Priorities**: Update official's interests and goals

---

## Tech Stack

- **Framework**: Laravel 12
- **Frontend**: Livewire 3, Alpine.js
- **Styling**: Tailwind CSS
- **Database**: SQLite (development), MySQL/PostgreSQL (production)
- **AI**: Anthropic Claude / OpenAI GPT for chat, analysis, and insights
- **APIs**: Congress.gov API for federal member data

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd legidash

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start development server
php artisan serve
```

## Configuration

### Environment Variables

Key environment variables to configure:

```env
# Application
APP_NAME="LegiDash"
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

# AI Features
AI_ENABLED=true
ANTHROPIC_API_KEY=your-anthropic-api-key
OPENAI_API_KEY=your-openai-api-key  # Optional

# Congress.gov API (for federal offices)
CONGRESS_API_KEY=your-congress-api-key
```

### Office Configuration

The `config/office.php` file is auto-generated by the Setup Wizard and contains:

- **Member Information**: Name, party, state, district, bio, social media
- **Government Level**: Federal, state, or local
- **Congressional Session**: Current Congress number and dates (federal only)
- **Committee Assignments**: Committee and subcommittee roles
- **District Information**: Cities, counties, population
- **Office Locations**: DC/Capitol and district office addresses with timezones
- **Staff Roles**: Role categories for dashboard routing
- **Legislative Activity**: URL for state/local bill tracking
- **News Sources**: Media outlets to monitor
- **Feature Flags**: Enable/disable specific features

## Database Schema

### Core Tables

| Table | Description |
|-------|-------------|
| `users` | Staff members with roles, titles, office locations |
| `issues` | Legislative issues and projects |
| `topics` | Issue/topic tags for meetings |
| `meetings` | Meeting records with types and preparation status |
| `people` | Stakeholders, advocates, and external contacts |
| `organizations` | Companies, agencies, advocacy groups |
| `actions` | Action items and follow-ups |
| `press_clips` | Media coverage tracking |

### Member Hub Tables

| Table | Description |
|-------|-------------|
| `member_profiles` | **Member priorities, interests, and AI settings** |
| `member_locations` | Track official's current location and activity |
| `member_statements` | Press releases, floor speeches, op-eds |
| `position_evolutions` | Policy position changes over time |
| `constituent_feedback` | Aggregated policy feedback themes (not casework) |
| `ai_insights` | AI-generated suggestions and alerts |
| `issue_relationships` | How policy areas connect |

### System Tables

| Table | Description |
|-------|-------------|
| `beta_requests` | Beta access request submissions |

## Project Structure

```
app/
├── Http/Controllers/     # HTTP controllers
├── Livewire/
│   ├── Admin/            # Admin components (AI, Billing, Settings)
│   ├── Dashboards/       # Dashboard components
│   ├── Issues/           # Issue management
│   ├── Management/       # Management components
│   ├── Meetings/         # Meeting components
│   ├── Media/            # Press and media
│   ├── MemberHub/        # Member context dashboard
│   ├── Organizations/    # Organization CRM
│   ├── People/           # Contact CRM
│   ├── PlatformAdmin/    # Platform admin (super admins only)
│   ├── Setup/            # Setup wizard & Member priorities
│   └── Team/             # Team management
├── Models/               # Eloquent models
├── Services/             # Business logic services
└── Jobs/                 # Background jobs

resources/
├── views/
│   ├── layouts/          # App layouts
│   ├── livewire/         # Livewire component views
│   │   ├── setup/        # Setup wizard & priorities views
│   │   ├── admin/        # Admin section views
│   │   └── platform-admin/ # Platform admin views
│   └── components/       # Blade components
├── css/
└── js/

config/
└── office.php            # Office configuration (auto-generated)
```

## Routes

| Route | Description |
|-------|-------------|
| `/` | Landing page with beta request |
| `/dashboard` | Auto-routes to appropriate dashboard |
| `/dashboard/personal` | Personal staff dashboard |
| `/dashboard/overview` | Office-wide overview |
| `/member` | Member Hub dashboard |
| `/setup` | Setup wizard |
| `/setup/priorities` | **Member Priorities questionnaire** |
| `/issues` | Issue management |
| `/meetings` | Meeting list and management |
| `/people` | Contact CRM |
| `/organizations` | Organization CRM |
| `/media` | Press and media tracking |
| `/knowledge` | Knowledge Hub (AI search) |
| `/knowledge-base` | Document library |
| `/team` | Team directory and hub |
| `/management/team` | **Team Overview (CoS/LD)** |
| `/admin/ai` | How AI Works (transparency) |
| `/admin/billing` | Billing & Plan |
| `/admin/settings` | Office Settings |
| `/platform-admin` | **Platform Admin (super admins only)** |

## Development History

### Phase 1: Core Renaming & Cleanup
- Renamed "Projects" module to "Issues" for congressional context
- Renamed old "Issues" (topic tags) to "Topics" to resolve naming conflict
- Removed "Funders" module (not applicable to congressional offices)
- Updated all routes, views, and navigation
- Added legislative fields to Issues (bill numbers, Congress, chamber)

### Phase 2: Member Hub Dashboard
- Created comprehensive Member Hub dashboard
- Added member location tracking with timezones
- Implemented policy position evolution tracking
- Added constituent feedback aggregation
- Created AI insights system
- Added member statements tracking
- Implemented dashboard routing based on staff roles
- Created office configuration system

### Phase 3: Multi-Level Support & Admin
- **Setup Wizard**: Support for federal, state, and local offices
- **Member Priorities**: 6-section questionnaire for capturing interests, goals, and AI settings
- **Beta System**: Request access form on homepage
- **Admin Restructure**: Split into Management (CoS/LD) and Admin sections
- **AI Transparency**: "How AI Works" page with prompt customization
- **Billing**: Beta pricing display
- **State/Local Support**: Legislative activity URL scraping
- **Social Media**: Added LinkedIn, Bluesky, TikTok support

### Phase 4: Platform Admin & Renaming
- Renamed from "WRK" to "LegiDash"
- Removed organization-specific references
- Added Platform Admin for super admins
- Generic branding for any legislative office

## Contributing

This is an internal tool for elected offices. For feature requests or bug reports, please contact the development team.

## License

Proprietary - All rights reserved.

---

**LegiDash** — Built for public service. 🏛️
