import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { X } from 'lucide-react';

export default function ProductForm({ product, onClose, onSaved }) {
    const [categories, setCategories] = useState([]);
    const [loadingCategories, setLoadingCategories] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        label: '',
        description: '',
        price: '',
        quantity: '',
        category_id: '',
        image: null,
    });

    const [imageUrl, setImageUrl] = useState(null);

    useEffect(() => {
        if (product) {
            setFormData({
                name: product.name || '',
                label: product.label || '',
                description: product.description || '',
                price: product.price || '',
                quantity: product.quantity || '',
                category_id: product.category_id || '',
                image: null,
            });
            setImageUrl(product.image ? `/storage/${product.image}` : null);
        }

        const fetchCategories = async () => {
            try {
                const res = await axios.get('/categories');
                setCategories(res.data);
            } catch (err) {
                console.error('Error fetching categories:', err);
            } finally {
                setLoadingCategories(false);
            }
        };

        fetchCategories();
    }, [product]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        setFormData((prev) => ({ ...prev, image: file }));

        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => setImageUrl(ev.target.result);
            reader.readAsDataURL(file);
        } else {
            setImageUrl(product && product.image ? `/storage/${product.image}` : null);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setError(null);

        const submitData = new FormData();

        // Append only non-empty values
        for (const key in formData) {
            const value = formData[key];
            if (value !== null && value !== undefined) {
                if (['label', 'description'].includes(key)) {
                    if (value !== '') {
                        submitData.append(key, value);
                    }
                } else if (key === 'image') {
                    if (value instanceof File) {
                        submitData.append(key, value);
                    }
                } else {
                    submitData.append(key, value);
                }
            }
        }

        try {
            let response;
            const url = product ? `/products/${product.id}` : '/products';

            if (product) {
                submitData.append('_method', 'PUT');
                response = await axios.post(url, submitData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
            } else {
                response = await axios.post(url, submitData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
            }

            // IMPORTANT: Get the category from our categories list
            const categoryId = response.data.category_id || formData.category_id;
            const category = categories.find(c => c.id == categoryId);

            // Create the full product object with category
            const fullProduct = {
                ...response.data,
                category: category || null
            };

            // Call onSaved with the updated/created product data
            onSaved(fullProduct);
        } catch (err) {
            console.error(
                'Error submitting product:',
                err.response ? err.response.data : err.message
            );

            if (err.response && err.response.data.errors) {
                const errorMessages = Object.values(err.response.data.errors).flat().join(', ');
                setError(errorMessages);
                alert(`Validation errors: ${errorMessages}`);
            } else {
                setError(err.message);
                alert(`Error saving product: ${err.message}`);
            }
        } finally {
            setSubmitting(false);
        }
    };

//     const handleSubmit = async (e) => {
//     e.preventDefault();
//     setSubmitting(true);
//     setError(null);

//     const submitData = new FormData();

//     console.log('=== FORM DATA BEFORE SUBMIT ===');
//     console.log('formData:', formData);

//     // Append only non-empty values
//     for (const key in formData) {
//         const value = formData[key];
//         if (value !== null && value !== undefined) {
//             if (['label', 'description'].includes(key)) {
//                 if (value !== '') {
//                     submitData.append(key, value);
//                 }
//             } else if (key === 'image') {
//                 if (value instanceof File) {
//                     submitData.append(key, value);
//                 }
//             } else {
//                 submitData.append(key, value);
//             }
//         }
//     }

//     // Log FormData contents
//     console.log('=== FORMDATA CONTENTS ===');
//     for (let [key, value] of submitData.entries()) {
//         console.log(`${key}:`, value);
//     }

//     try {
//         let response;
//         const url = product ? `/products/${product.id}` : '/products';

//         console.log('=== MAKING API REQUEST ===');
//         console.log('URL:', url);
//         console.log('Is edit mode?', !!product);

//         if (product) {
//             submitData.append('_method', 'PUT');
//             response = await axios.post(url, submitData, {
//                 headers: {
//                     'Content-Type': 'multipart/form-data',
//                 },
//             });
//         } else {
//             response = await axios.post(url, submitData, {
//                 headers: {
//                     'Content-Type': 'multipart/form-data',
//                 },
//             });
//         }

//         console.log('=== API RESPONSE ===');
//         console.log('Full response:', response);
//         console.log('Response data:', response.data);
//         console.log('Response has category?', !!response.data.category);
//         console.log('Response category_id:', response.data.category_id);

//         // IMPORTANT: Get the category from our categories list
//         const categoryId = response.data.category_id || formData.category_id;
//         console.log('Category ID to find:', categoryId);

//         const category = categories.find(c => c.id == categoryId);
//         console.log('Found category:', category);

//         // Create the full product object with category
//         const fullProduct = {
//             ...response.data,
//             category: category || null
//         };

//         console.log('=== FULL PRODUCT TO SEND ===');
//         console.log('fullProduct:', fullProduct);
//         console.log('fullProduct.id:', fullProduct.id);
//         console.log('fullProduct.category:', fullProduct.category);

//         // Call onSaved with the updated/created product data
//         onSaved(fullProduct);
//     } catch (err) {
//         console.error(
//             'Error submitting product:',
//             err.response ? err.response.data : err.message
//         );

//         if (err.response && err.response.data.errors) {
//             const errorMessages = Object.values(err.response.data.errors).flat().join(', ');
//             setError(errorMessages);
//             alert(`Validation errors: ${errorMessages}`);
//         } else {
//             setError(err.message);
//             alert(`Error saving product: ${err.message}`);
//         }
//     } finally {
//         setSubmitting(false);
//     }
// };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4">
            <div className="max-h-[90vh] w-full max-w-md md:max-w-lg overflow-y-auto bg-white p-4 md:p-6 rounded-xl shadow-2xl">
                <div className="flex justify-between items-center mb-4">
                    <h3 className="text-xl md:text-2xl font-bold text-gray-800">
                        {product ? 'Edit Product' : 'Add New Product'}
                    </h3>
                    <button
                        onClick={onClose}
                        className="p-1 hover:bg-gray-100 rounded-full transition-colors"
                    >
                        <X className="h-5 w-5 text-gray-500" />
                    </button>
                </div>

                {error && (
                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p className="text-red-600 text-sm">{error}</p>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-3 md:space-y-4">
                    {/* Name */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input
                            type="text"
                            name="name"
                            value={formData.name}
                            onChange={handleChange}
                            required
                            className="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>

                    {/* Price */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                        <input
                            type="number"
                            name="price"
                            value={formData.price}
                            onChange={handleChange}
                            required
                            step="0.01"
                            min="0"
                            className="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>

                    {/* Quantity */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input
                            type="number"
                            name="quantity"
                            value={formData.quantity}
                            onChange={handleChange}
                            required
                            min="0"
                            className="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="0"
                        />
                    </div>

                    {/* Category */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select
                            name="category_id"
                            value={formData.category_id}
                            onChange={handleChange}
                            required
                            className="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">-- Select Category --</option>
                            {loadingCategories ? (
                                <option disabled>Loading categories...</option>
                            ) : (
                                categories.map((cat) => (
                                    <option key={cat.id} value={cat.id}>
                                        {cat.name}
                                    </option>
                                ))
                            )}
                        </select>
                    </div>

                    {/* Label */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Label (Optional)</label>
                        <input
                            type="text"
                            name="label"
                            value={formData.label}
                            onChange={handleChange}
                            className="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>

                    {/* Description */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea
                            name="description"
                            value={formData.description}
                            onChange={handleChange}
                            rows="2"
                            className="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                        />
                    </div>

                    {/* Image */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Product Image {!product && '(Optional)'}
                        </label>
                        <input
                            type="file"
                            name="image"
                            onChange={handleFileChange}
                            accept="image/*"
                            className="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />

                        {imageUrl && (
                            <div className="mt-2">
                                <p className="text-xs text-gray-500 mb-1">Preview:</p>
                                <img
                                    src={imageUrl}
                                    alt="Preview"
                                    className="h-20 w-20 object-cover rounded-md border shadow-sm"
                                />
                            </div>
                        )}
                    </div>

                    {/* Buttons */}
                    <div className="flex justify-end gap-3 pt-4 border-t">
                        <button
                            type="button"
                            onClick={onClose}
                            disabled={submitting}
                            className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors disabled:opacity-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={submitting}
                            className="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
                        >
                            {submitting && (
                                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                            )}
                            {product ? 'Update Product' : 'Create Product'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
