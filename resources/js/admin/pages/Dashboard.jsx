import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { 
  Package, ShoppingCart, Tag, DollarSign, 
  Monitor, Keyboard, Cpu, Headphones, Printer,
  Smartphone, HardDrive, Mouse, Camera, Laptop
} from 'lucide-react';

// Define a simple reusable card component for stats
const StatCard = ({ title, value, icon: Icon, colorClass, trend = null, description = '' }) => (
  <div className={`p-5 bg-white rounded-xl shadow-lg border-l-4 ${colorClass}`}>
    <div className="flex justify-between items-center">
      <div>
        <p className="text-sm font-medium text-gray-500 uppercase tracking-wider">{title}</p>
        <p className="text-3xl font-bold text-gray-900 mt-1">{value}</p>
        {trend && (
          <p className={`text-sm mt-1 ${trend.startsWith('+') ? 'text-green-600' : 'text-red-600'}`}>
            {trend}
          </p>
        )}
        {description && (
          <p className="text-xs text-gray-500 mt-1">{description}</p>
        )}
      </div>
      <div className={`p-3 rounded-full ${colorClass.replace('border-l-4 ', '').replace('border-', 'bg-opacity-20 ')} text-gray-700`}>
        <Icon className="h-6 w-6" />
      </div>
    </div>
  </div>
);

// Subcategory square component
const SubCategoryCard = ({ icon: Icon, title, count, color = 'bg-blue-100' }) => (
  <div className={`p-4 rounded-xl ${color} flex flex-col items-center justify-center transition-transform hover:scale-105`}>
    <div className="p-3 bg-white rounded-full mb-2 shadow-sm">
      <Icon className="h-6 w-6 text-gray-700" />
    </div>
    <p className="text-lg font-semibold text-gray-900">{count}</p>
    <p className="text-xs text-gray-600 text-center mt-1">{title}</p>
  </div>
);

export default function Dashboard() {
  const [stats, setStats] = useState({
    totalProducts: 0,
    totalCategories: 0,
    totalRevenue: 0,
    newOrders: 0,
    categoryBreakdown: {},
    loading: true
  });

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      // Fetch products and categories in parallel
      const [productsRes, categoriesRes] = await Promise.all([
        axios.get('/products'),
        axios.get('/categories')
      ]);

      const products = productsRes.data;
      const categories = categoriesRes.data;

      // Calculate category breakdown
      const categoryBreakdown = {};
      products.forEach(product => {
        if (product.category) {
          const categoryName = product.category.name;
          categoryBreakdown[categoryName] = (categoryBreakdown[categoryName] || 0) + 1;
        }
      });

      // Calculate total revenue (sample calculation)
      const totalRevenue = products.reduce((sum, product) => {
        return sum + (parseFloat(product.price) || 0);
      }, 0);

      setStats({
        totalProducts: products.length,
        totalCategories: categories.length,
        totalRevenue,
        newOrders: 0, // You would fetch this from orders API
        categoryBreakdown,
        loading: false
      });
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
      setStats(prev => ({ ...prev, loading: false }));
    }
  };

  // Icon mapping for categories
  const categoryIcons = {
    'Laptops': Laptop,
    'Monitors': Monitor,
    'Keyboards': Keyboard,
    'Mouse': Mouse,
    'Printers': Printer,
    'Cameras': Camera,
    'Audio': Headphones,
    'Storage': HardDrive,
    'Networking': Cpu,
    'Accessories': Smartphone,
    'Default': Package
  };

  // Color mapping for categories
  const categoryColors = {
    'Laptops': 'bg-blue-100',
    'Monitors': 'bg-green-100',
    'Keyboards': 'bg-yellow-100',
    'Mouse': 'bg-purple-100',
    'Printers': 'bg-pink-100',
    'Cameras': 'bg-indigo-100',
    'Audio': 'bg-red-100',
    'Storage': 'bg-orange-100',
    'Networking': 'bg-teal-100',
    'Accessories': 'bg-cyan-100',
    'Default': 'bg-gray-100'
  };

  // Get top categories (sorted by count)
  const topCategories = Object.entries(stats.categoryBreakdown)
    .sort(([, a], [, b]) => b - a)
    .slice(0, 8); // Show top 8 categories

  return (
    <div className="space-y-8 p-6">
      {/* Header Section */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-3xl font-extrabold text-gray-900">
            Admin Dashboard
          </h2>
          <p className="text-gray-600 mt-1">Real-time overview of your store</p>
        </div>
        <div className="flex gap-3">
          <button className="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-150 shadow-sm">
            Export Data
          </button>
          <button className="px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md">
            Refresh Data
          </button>
        </div>
      </div>

      {/* Loading State */}
      {stats.loading ? (
        <div className="flex justify-center items-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
      ) : (
        <>
          {/* Stat Cards Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard
              title="Total Products"
              value={stats.totalProducts}
              icon={Package}
              colorClass="border-blue-500"
              description="Across all categories"
            />
            <StatCard
              title="Total Categories"
              value={stats.totalCategories}
              icon={Tag}
              colorClass="border-green-500"
              description="Product categories"
            />
            <StatCard
              title="Total Inventory Value"
              value={`Rs.${stats.totalRevenue.toLocaleString()}`}
              icon={DollarSign}
              colorClass="border-yellow-500"
              description="Total stock value"
            />
            <StatCard
              title="New Orders"
              value={stats.newOrders}
              icon={ShoppingCart}
              colorClass="border-red-500"
              trend="+12% this month"
              description="Pending orders"
            />
          </div>

          {/* Category Breakdown Section */}
          <div className="bg-white rounded-xl shadow-lg p-6">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl font-semibold text-gray-800">
                Product Distribution by Category
              </h3>
              <span className="text-sm text-gray-500">
                {Object.values(stats.categoryBreakdown).reduce((a, b) => a + b, 0)} items total
              </span>
            </div>
            
            {/* Subcategory Squares Grid */}
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
              {topCategories.length > 0 ? (
                topCategories.map(([categoryName, count]) => {
                  const Icon = categoryIcons[categoryName] || categoryIcons['Default'];
                  const color = categoryColors[categoryName] || categoryColors['Default'];
                  return (
                    <SubCategoryCard
                      key={categoryName}
                      icon={Icon}
                      title={categoryName}
                      count={count}
                      color={color}
                    />
                  );
                })
              ) : (
                <div className="col-span-full text-center py-8 text-gray-500">
                  No category data available
                </div>
              )}
            </div>

            {/* Category Progress Bars */}
            <div className="mt-8 space-y-4">
              <h4 className="font-medium text-gray-700">Detailed Category Breakdown</h4>
              {Object.entries(stats.categoryBreakdown)
                .sort(([, a], [, b]) => b - a)
                .map(([categoryName, count]) => {
                  const percentage = (count / stats.totalProducts * 100).toFixed(1);
                  return (
                    <div key={categoryName} className="space-y-1">
                      <div className="flex justify-between text-sm">
                        <span className="font-medium text-gray-700">{categoryName}</span>
                        <span className="text-gray-600">{count} items ({percentage}%)</span>
                      </div>
                      <div className="w-full bg-gray-200 rounded-full h-2">
                        <div 
                          className="bg-indigo-600 h-2 rounded-full transition-all duration-500"
                          style={{ width: `${percentage}%` }}
                        ></div>
                      </div>
                    </div>
                  );
                })}
            </div>
          </div>

          {/* Main Content Area */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Welcome Panel */}
            <div className="lg:col-span-2 p-6 bg-white rounded-xl shadow-lg">
              <h3 className="text-xl font-semibold text-gray-800 mb-4">
                Store Overview
              </h3>
              <div className="space-y-4">
                <p className="text-gray-600">
                  Your store currently has <span className="font-semibold text-indigo-600">{stats.totalProducts} products</span> 
                  {' '}organized across <span className="font-semibold text-indigo-600">{stats.totalCategories} categories</span>.
                </p>
                <div className="bg-blue-50 p-4 rounded-lg">
                  <h4 className="font-medium text-blue-800 mb-2">📈 Top Performing Categories</h4>
                  <ul className="space-y-2">
                    {topCategories.slice(0, 3).map(([category, count]) => (
                      <li key={category} className="flex justify-between text-sm">
                        <span className="text-gray-700">{category}</span>
                        <span className="font-medium">{count} products</span>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            </div>

            {/* Quick Actions Panel */}
            <div className="p-6 bg-white rounded-xl shadow-lg">
              <h3 className="text-xl font-semibold text-gray-800 mb-4">
                Quick Actions
              </h3>
              <ul className="space-y-3">
                <li>
                  <a 
                    href="/admin/products" 
                    className="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                  >
                    <Package className="h-4 w-4" />
                    + Add New Product
                  </a>
                </li>
                <li>
                  <a 
                    href="/admin/orders" 
                    className="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                  >
                    <ShoppingCart className="h-4 w-4" />
                    Review Pending Orders ({stats.newOrders})
                  </a>
                </li>
                <li>
                  <a 
                    href="/admin/categories" 
                    className="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                  >
                    <Tag className="h-4 w-4" />
                    Manage Categories
                  </a>
                </li>
                <li>
                  <a 
                    href="/admin/analytics" 
                    className="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                  >
                    <DollarSign className="h-4 w-4" />
                    View Sales Analytics
                  </a>
                </li>
              </ul>
            </div>
          </div>

          {/* Recent Activity */}
          <div className="bg-white rounded-xl shadow-lg p-6">
            <h3 className="text-xl font-semibold text-gray-800 mb-4">
              Recent Store Activity
            </h3>
            <div className="space-y-4">
              <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-green-100 rounded-full">
                    <Package className="h-4 w-4 text-green-600" />
                  </div>
                  <div>
                    <p className="font-medium text-gray-900">New Products Added</p>
                    <p className="text-sm text-gray-500">Today, 10:30 AM</p>
                  </div>
                </div>
                <span className="text-sm text-gray-600">{stats.totalProducts} total</span>
              </div>
              
              <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-blue-100 rounded-full">
                    <Tag className="h-4 w-4 text-blue-600" />
                  </div>
                  <div>
                    <p className="font-medium text-gray-900">Categories Updated</p>
                    <p className="text-sm text-gray-500">Today, 09:15 AM</p>
                  </div>
                </div>
                <span className="text-sm text-gray-600">{stats.totalCategories} active</span>
              </div>
            </div>
          </div>

          {/* Footer */}
          <div className="text-center text-sm text-gray-500 pt-4">
            <p>Dashboard updated just now • Total inventory value: Rs.{stats.totalRevenue.toLocaleString()}</p>
          </div>
        </>
      )}
    </div>
  );
}