import React, { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import { Search, Edit2, Trash2, Plus, X, Filter } from 'lucide-react';
import ProductForm from '../components/ProductForm';

// Assuming your Laravel public disk is served at /storage
const IMAGE_BASE_URL = '/storage/';

export default function Products() {
    const [products, setProducts] = useState([]);
    const [filteredProducts, setFilteredProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [editingProduct, setEditingProduct] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [isSearching, setIsSearching] = useState(false);

    // Function to fetch products
    const fetchProducts = useCallback(async () => {
        setLoading(true);
        try {
            const response = await axios.get('/products');
            setProducts(response.data);
            setFilteredProducts(response.data);
        } catch (error) {
            console.error("Error fetching products:", error);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchProducts();
    }, [fetchProducts]);

    // Search functionality
    useEffect(() => {
        if (!searchTerm.trim()) {
            setFilteredProducts(products);
            setIsSearching(false);
            return;
        }

        setIsSearching(true);
        const term = searchTerm.toLowerCase();
        const filtered = products.filter(product =>
            product.name.toLowerCase().includes(term) ||
            (product.label && product.label.toLowerCase().includes(term)) ||
            (product.description && product.description.toLowerCase().includes(term)) ||
            (product.category && product.category.name.toLowerCase().includes(term)) ||
            product.price.toString().includes(term)
        );
        setFilteredProducts(filtered);
    }, [searchTerm, products]);

    // Handler for Delete - Optimistic update
    const handleDelete = async (id) => {
        if (!window.confirm('Are you sure you want to delete this product?')) {
            return;
        }

        // Optimistic update: Remove from UI immediately
        setProducts(prev => prev.filter(p => p.id !== id));
        setFilteredProducts(prev => prev.filter(p => p.id !== id));

        try {
            await axios.delete(`/products/${id}`);
        } catch (error) {
            console.error("Error deleting product:", error);
            // Revert on error
            fetchProducts();
        }
    };

    // Handler for Add/Edit Form
    const handleAdd = () => {
        setEditingProduct(null);
        setShowForm(true);
    };

    const handleEdit = (product) => {
        setEditingProduct(product);
        setShowForm(true);
    };

    const handleFormClose = () => {
        setShowForm(false);
        setEditingProduct(null);
    };


const handleSaved = async (savedProduct) => {
    console.log('=== HANDLE SAVED CALLED ===');

    setShowForm(false);

    if (editingProduct) {
        console.log('Updating existing product');
        // We were editing an existing product
        setProducts(prev => prev.map(p =>
            p.id === savedProduct.id ? savedProduct : p
        ));
        setFilteredProducts(prev => prev.map(p =>
            p.id === savedProduct.id ? savedProduct : p
        ));
    } else {
        console.log('Adding new product');
        // We were adding a new product
        setProducts(prev => [savedProduct, ...prev]);
        setFilteredProducts(prev => [savedProduct, ...prev]);
    }

    setEditingProduct(null);
};
    // Clear search
    const clearSearch = () => {
        setSearchTerm('');
    };

    return (
        <div className="p-4 md:p-6">
            {/* Header with Add button and Search */}
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h2 className="text-2xl md:text-3xl font-bold text-gray-800">Product Management</h2>
                    <p className="text-sm text-gray-600 mt-1">
                        {filteredProducts.length} product{filteredProducts.length !== 1 ? 's' : ''} found
                        {isSearching && ` for "${searchTerm}"`}
                    </p>
                </div>

                <div className="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    {/* Search Bar */}
                    <div className="relative w-full sm:w-64">
                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                        <input
                            type="text"
                            placeholder="Search products..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                        />
                        {searchTerm && (
                            <button
                                onClick={clearSearch}
                                className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        )}
                    </div>

                    {/* Add Product Button */}
                    <button
                        onClick={handleAdd}
                        className="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md whitespace-nowrap"
                    >
                        <Plus className="h-4 w-4" />
                        Add New Product
                    </button>
                </div>
            </div>

            {/* Products Table - Responsive Container */}
            <div className="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
                {loading ? (
                    <div className="flex justify-center items-center py-12">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    </div>
                ) : filteredProducts.length === 0 ? (
                    <div className="text-center py-12">
                        <Filter className="h-12 w-12 text-gray-300 mx-auto mb-3" />
                        <h3 className="text-lg font-medium text-gray-900 mb-1">
                            {isSearching ? 'No matching products found' : 'No products available'}
                        </h3>
                        <p className="text-gray-500 text-sm">
                            {isSearching
                                ? 'Try a different search term'
                                : 'Click "Add New Product" to create your first product'}
                        </p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Image</th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Label</th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Category</th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {filteredProducts.map((product) => (
                                    <tr key={product.id} className="hover:bg-gray-50 transition-colors duration-150">
                                        <td className="px-4 md:px-6 py-4 whitespace-nowrap">
                                            <div className="h-10 w-10 flex-shrink-0">
                                                {product.image ? (
                                                    <img
                                                        src={IMAGE_BASE_URL + product.image}
                                                        alt={product.name}
                                                        className="h-10 w-10 object-cover rounded-md"
                                                        onError={(e) => {
                                                            e.target.onerror = null;
                                                            e.target.src = 'https://via.placeholder.com/40';
                                                        }}
                                                    />
                                                ) : (
                                                    <div className="h-10 w-10 bg-gray-100 rounded-md flex items-center justify-center">
                                                        <span className="text-xs text-gray-400">No Image</span>
                                                    </div>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-4 md:px-6 py-4">
                                            <div className="text-sm font-medium text-gray-900 truncate max-w-[150px] md:max-w-xs">
                                                {product.name}
                                            </div>
                                            <div className="text-xs text-gray-500 md:hidden">
                                                {product.category ? product.category.name : 'N/A'}
                                            </div>
                                        </td>
                                        <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                            {product.label || '-'}
                                        </td>
                                        <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                                            <span className="px-2 py-1 text-xs rounded-full bg-gray-100">
                                                {product.category ? product.category.name : 'N/A'}
                                            </span>
                                        </td>
                                        <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            Rs.{parseFloat(product.price).toFixed(2)}
                                        </td>
                                        <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {product.quantity ? product.quantity : 'N/A'}
                                        </td>
                                        <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div className="flex items-center gap-2">
                                                <button
                                                    onClick={() => handleEdit(product)}
                                                    className="p-1.5 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition-colors duration-150"
                                                    title="Edit"
                                                >
                                                    <Edit2 className="h-4 w-4" />
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(product.id)}
                                                    className="p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-colors duration-150"
                                                    title="Delete"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Mobile-only simplified view for small screens */}
            {!loading && filteredProducts.length > 0 && (
                <div className="md:hidden mt-4 space-y-3">
                    {filteredProducts.map((product) => (
                        <div key={product.id} className="bg-white p-4 rounded-lg shadow border border-gray-200">
                            <div className="flex items-start gap-3">
                                <div className="flex-shrink-0">
                                    {product.image ? (
                                        <img
                                            src={IMAGE_BASE_URL + product.image}
                                            alt={product.name}
                                            className="h-12 w-12 object-cover rounded-md"
                                        />
                                    ) : (
                                        <div className="h-12 w-12 bg-gray-100 rounded-md flex items-center justify-center">
                                            <span className="text-xs text-gray-400">No Image</span>
                                        </div>
                                    )}
                                </div>
                                <div className="flex-1 min-w-0">
                                    <h4 className="text-sm font-medium text-gray-900 truncate">{product.name}</h4>
                                    <p className="text-xs text-gray-500 mt-1">{product.label || 'No label'}</p>
                                    <div className="flex items-center justify-between mt-2">
                                        <span className="text-sm font-semibold text-gray-900">
                                            ${parseFloat(product.price).toFixed(2)}
                                        </span>
                                        <div className="flex items-center gap-2">
                                            <button
                                                onClick={() => handleEdit(product)}
                                                className="text-indigo-600 text-sm font-medium"
                                            >
                                                Edit
                                            </button>
                                            <span className="text-gray-300">|</span>
                                            <button
                                                onClick={() => handleDelete(product.id)}
                                                className="text-red-600 text-sm font-medium"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Product Form Modal */}
            {showForm && (
                <ProductForm
                    product={editingProduct}
                    onClose={handleFormClose}
                    onSaved={handleSaved} // Only update specific product
                />
            )}
        </div>
    );
}
