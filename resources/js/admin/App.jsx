import React from "react";
import { Routes, Route, Link } from "react-router-dom";
import Dashboard from "./pages/Dashboard";
import Products from "./pages/Products";
import Categories from "./pages/Categories";
import Brands from "./pages/Brands";

export default function App() {
    return (
        <div className="flex min-h-screen bg-gray-100">

            {/* Sidebar */}
            <aside className="w-64 bg-white shadow-md p-5">
                <h1 className="text-xl font-bold mb-6">Admin Panel</h1>

                <nav className="flex flex-col gap-3">
                    <Link
                        to="/"
                        className="p-2 hover:bg-gray-200 rounded"
                    >
                        Dashboard
                    </Link>
                    <Link
                        to="/products"
                        className="p-2 hover:bg-gray-200 rounded"
                    >
                        Products
                    </Link>
                    <Link
                        to="/categories"
                        className="p-2 hover:bg-gray-200 rounded"
                    >
                        Categories
                    </Link>
                    <Link
                        to="/brands"
                        className="p-2 hover:bg-gray-200 rounded"
                    >
                        Brands
                    </Link>
                </nav>
            </aside>

            {/* Main Content */}
            <main className="flex-1 p-6">
                <Routes>
                    <Route path="/" element={<Dashboard />} />
                    <Route path="/products" element={<Products />} />
                    <Route path="/categories" element={<Categories />} />
                    <Route path="/brands" element={<Brands />} />
                </Routes>
            </main>
        </div>
    );
}
