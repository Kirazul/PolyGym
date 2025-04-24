# PolyGym - Modern Fitness Club Management System

<p align="center">
  <img src="https://via.placeholder.com/150?text=PolyGym" alt="PolyGym Logo" width="200"/>
</p>

## Overview

PolyGym is a comprehensive fitness club management system designed to streamline operations for gym owners and enhance experience for members. The platform integrates membership management, class scheduling, trainer allocation, equipment tracking, and billing in a single, intuitive interface.

## Features

- **Member Management**
  - Registration and profile management
  - Membership plans and renewals
  - Attendance tracking with QR code/biometric options

- **Class & Scheduling**
  - Interactive class calendar
  - Online class bookings
  - Capacity management
  - Automated reminders

- **Trainer Management**
  - Trainer profiles and specializations
  - Availability tracking
  - Session booking and management

- **Billing & Payments**
  - Automated billing cycles
  - Multiple payment methods
  - Invoice generation
  - Subscription management

- **Equipment & Facility**
  - Equipment inventory
  - Maintenance scheduling
  - Usage analytics

- **Analytics Dashboard**
  - Membership trends
  - Revenue reports
  - Class popularity metrics
  - Retention analytics

- **Mobile Application**
  - iOS and Android compatibility
  - Member portal
  - Workout tracking
  - Progress monitoring

## Tech Stack

- **Frontend**: React.js, Redux, Material-UI
- **Backend**: Node.js, Express
- **Database**: MongoDB
- **Authentication**: JWT, OAuth
- **Payments**: Stripe Integration
- **Hosting**: AWS/Heroku
- **CI/CD**: GitHub Actions

## Installation

```bash
# Clone the repository
git clone https://github.com/Kirazul/PolyGym.git

# Navigate to the project directory
cd PolyGym

# Install dependencies
npm install

# Set up environment variables
cp .env.example .env
# Edit .env with your configuration

# Start development server
npm run dev
```

## Environment Setup

Create a `.env` file in the root directory with the following variables:

```
PORT=3000
MONGODB_URI=your_mongodb_connection_string
JWT_SECRET=your_jwt_secret
STRIPE_API_KEY=your_stripe_key
```

## Usage

1. Access the admin dashboard at `http://localhost:3000/admin`
2. Default admin credentials:
   - Username: admin
   - Password: admin123
3. Change the default credentials after first login

## API Documentation

API documentation is available at `/api/docs` when the server is running.

Main endpoints:

- `/api/members` - Member management
- `/api/classes` - Class scheduling
- `/api/trainers` - Trainer information
- `/api/payments` - Billing and payments
- `/api/stats` - Analytics and reporting

## Deployment

```bash
# Build for production
npm run build

# Start production server
npm start
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Screenshots

<p align="center">
  <img src="https://via.placeholder.com/800x400?text=Dashboard+Screenshot" alt="Dashboard" width="800"/>
</p>

<p align="center">
  <img src="https://via.placeholder.com/800x400?text=Mobile+App+Screenshot" alt="Mobile App" width="800"/>
</p>

## Roadmap

- Nutritional planning integration
- Personal trainer marketplace
- Wearable device synchronization
- Virtual class streaming
- AI-powered workout recommendations

## Contact

Project Link: [https://github.com/Kirazul/PolyGym](https://github.com/Kirazul/PolyGym)

## Acknowledgements

- [Font Awesome](https://fontawesome.com)
- [React Icons](https://react-icons.github.io/react-icons)
- [Material-UI](https://material-ui.com)
- [Chart.js](https://www.chartjs.org)
- [MongoDB Atlas](https://www.mongodb.com/cloud/atlas) 