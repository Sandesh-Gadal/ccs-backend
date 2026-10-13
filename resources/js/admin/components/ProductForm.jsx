import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { X, ImagePlus, ChevronLeft, ChevronRight, Star } from 'lucide-react';

const MAX_PHOTOS = 8;

export default function ProductForm({ product, onClose, onSaved }) {
    const [categories, setCategories] = useState([]);
    const [brands, setBrands] = useState([]);
    const [loadingCategories, setLoadingCategories] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        label: '',
        description: '',
        price: '',
        mrp: '',
        quantity: '',
        category_id: '',
        brand_id: '',
    });

    // Gallery: saved photos and new uploads in display order; the first one is the cover
    const [photos, setPhotos] = useState([]);
    // New products are published by default
    const [published, setPublished] = useState(product ? product.is_published !== false : true);
    const [removedIds, setRemovedIds] = useState([]);
    const [dragOver, setDragOver] = useState(false);

    useEffect(() => {
        if (product) {
            setFormData({
                name: product.name || '',
                label: product.label || '',
                description: product.description || '',
                price: product.price || '',
                mrp: product.mrp || '',
                quantity: product.quantity || '',
                category_id: product.category_id || '',
                brand_id: product.brand_id || '',
            });
            const saved = product.images?.length
                ? product.images
                : [];
            setPhotos(saved.map((img) => ({ key: `e${img.id}`, kind: 'existing', id: img.id, url: `/storage/${img.path}` })));
            setRemovedIds([]);
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
        axios.get('/brands').then((res) => setBrands(res.data)).catch(() => {});
    }, [product]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const addFiles = (fileList) => {
        const files = Array.from(fileList || []).filter((f) => f.type.startsWith('image/'));
        setPhotos((prev) => {
            const room = MAX_PHOTOS - prev.length;
            if (files.length > room) {
                alert(`A product can have at most ${MAX_PHOTOS} photos. Only the first ${Math.max(room, 0)} were added.`);
            }
            const added = files.slice(0, Math.max(room, 0)).map((file, i) => ({
                key: `n-${Date.now()}-${i}-${file.name}`,
                kind: 'new',
                file,
                url: URL.createObjectURL(file),
            }));
            return [...prev, ...added];
        });
    };

    const handleFileChange = (e) => {
        addFiles(e.target.files);
        e.target.value = '';
    };

    const removePhoto = (key) => {
        setPhotos((prev) => {
            const photo = prev.find((p) => p.key === key);
            if (photo?.kind === 'existing') setRemovedIds((ids) => [...ids, photo.id]);
            if (photo?.kind === 'new') URL.revokeObjectURL(photo.url);
            return prev.filter((p) => p.key !== key);
        });
    };

    const movePhoto = (index, delta) => {
        setPhotos((prev) => {
            const target = index + delta;
            if (target < 0 || target >= prev.length) return prev;
            const next = [...prev];
            [next[index], next[target]] = [next[target], next[index]];
            return next;
        });
    };

    const makeCover = (index) => {
        setPhotos((prev) => [prev[index], ...prev.filter((_, i) => i !== index)]);
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
                } else {
                    submitData.append(key, value);
                }
            }
        }

        // Photos: new files as images[], plus the full order ("e<id>" saved, "n<index>" new)
        let newIndex = 0;
        photos.forEach((photo) => {
            if (photo.kind === 'new') {
                submitData.append('images[]', photo.file);
                submitData.append('image_order[]', `n${newIndex++}`);
            } else {
                submitData.append('image_order[]', `e${photo.id}`);
            }
        });
        removedIds.forEach((id) => submitData.append('remove_images[]', id));
        submitData.append('is_published', published ? '1' : '0');

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
            const brand = brands.find((b) => b.id == (response.data.brand_id || formData.brand_id));

            // Create the full product object with category
            const fullProduct = {
                ...response.data,
                category: category || null,
                brand: brand || null,
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

                    {/* Prices */}
                    <div>
                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">MRP (Rs.)</label>
                                <input
                                    type="number"
                                    name="mrp"
                                    value={formData.mrp}
                                    onChange={handleChange}
                                    step="0.01"
                                    min="0"
                                    placeholder="Optional"
                                    className="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Sale price (Rs.) *</label>
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
                        </div>
                        {(() => {
                            const mrp = parseFloat(formData.mrp);
                            const price = parseFloat(formData.price);
                            if (!(mrp > 0) || !(price > 0)) {
                                return <p className="mt-1 text-xs text-gray-500">Leave MRP empty if there's no discount. The website shows MRP crossed out when it's higher than the sale price.</p>;
                            }
                            if (mrp < price) {
                                return <p className="mt-1 text-xs text-red-600">MRP must be equal to or higher than the sale price.</p>;
                            }
                            if (mrp === price) {
                                return <p className="mt-1 text-xs text-gray-500">No discount: only the sale price will be shown.</p>;
                            }
                            const save = mrp - price;
                            return (
                                <p className="mt-1 text-xs text-emerald-700">
                                    Shows as <span className="line-through text-gray-400">Rs.{mrp.toLocaleString('en-IN')}</span>{' '}
                                    <span className="font-semibold">Rs.{price.toLocaleString('en-IN')}</span> · {Math.round((save / mrp) * 100)}% off, customer saves Rs.{save.toLocaleString('en-IN')}
                                </p>
                            );
                        })()}
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

                    {/* Brand */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Brand (Optional)</label>
                        <select
                            name="brand_id"
                            value={formData.brand_id}
                            onChange={handleChange}
                            className="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">-- No brand --</option>
                            {brands.map((b) => (
                                <option key={b.id} value={b.id}>
                                    {b.name}
                                </option>
                            ))}
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

                    {/* Photos */}
                    <div>
                        <div className="flex items-baseline justify-between mb-1">
                            <label className="block text-sm font-medium text-gray-700">Product Photos</label>
                            <span className="text-xs text-gray-500">{photos.length}/{MAX_PHOTOS} · first photo is the cover</span>
                        </div>

                        {photos.length > 0 && (
                            <div className="grid grid-cols-4 gap-2 mb-2">
                                {photos.map((photo, index) => (
                                    <div key={photo.key} className="group relative aspect-square rounded-md border bg-gray-50 overflow-hidden">
                                        <img src={photo.url} alt="" className="h-full w-full object-contain p-1" />
                                        {index === 0 && (
                                            <span className="absolute left-1 top-1 rounded bg-indigo-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">Cover</span>
                                        )}
                                        {photo.kind === 'new' && (
                                            <span className="absolute right-1 bottom-1 rounded bg-emerald-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">New</span>
                                        )}
                                        <button
                                            type="button"
                                            onClick={() => removePhoto(photo.key)}
                                            className="absolute right-1 top-1 rounded-full bg-white/90 p-0.5 text-gray-700 shadow hover:bg-red-500 hover:text-white"
                                            title="Remove photo"
                                        >
                                            <X size={14} />
                                        </button>
                                        <div className="absolute inset-x-0 bottom-0 flex justify-center gap-1 bg-black/50 py-1 opacity-0 transition-opacity group-hover:opacity-100">
                                            <button type="button" onClick={() => movePhoto(index, -1)} disabled={index === 0} className="rounded p-0.5 text-white hover:bg-white/20 disabled:opacity-30" title="Move left">
                                                <ChevronLeft size={14} />
                                            </button>
                                            {index !== 0 && (
                                                <button type="button" onClick={() => makeCover(index)} className="rounded p-0.5 text-white hover:bg-white/20" title="Make cover">
                                                    <Star size={14} />
                                                </button>
                                            )}
                                            <button type="button" onClick={() => movePhoto(index, 1)} disabled={index === photos.length - 1} className="rounded p-0.5 text-white hover:bg-white/20 disabled:opacity-30" title="Move right">
                                                <ChevronRight size={14} />
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}

                        {photos.length < MAX_PHOTOS && (
                            <label
                                onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                                onDragLeave={() => setDragOver(false)}
                                onDrop={(e) => { e.preventDefault(); setDragOver(false); addFiles(e.dataTransfer.files); }}
                                className={`flex cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed p-4 text-center text-sm transition-colors ${
                                    dragOver ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-500 hover:border-indigo-400 hover:bg-gray-50'
                                }`}
                            >
                                <ImagePlus size={22} />
                                <span><span className="font-semibold text-indigo-600">Click to add photos</span> or drag them here</span>
                                <span className="text-xs text-gray-400">JPG, PNG or WebP, up to 4 MB each</span>
                                <input type="file" accept="image/jpeg,image/png,image/webp" multiple onChange={handleFileChange} className="sr-only" />
                            </label>
                        )}
                    </div>

                    {/* Visibility */}
                    <label className="flex cursor-pointer items-start justify-between gap-4 rounded-lg border border-gray-200 p-3">
                        <span>
                            <span className="block text-sm font-medium text-gray-800">
                                {published ? 'Published' : 'Draft (hidden)'}
                            </span>
                            <span className="block text-xs text-gray-500">
                                {published
                                    ? 'Visible on the website.'
                                    : "Saved in the admin panel only. Customers can't see or open it."}
                            </span>
                        </span>
                        <span className="relative mt-0.5 inline-flex flex-shrink-0">
                            <input
                                type="checkbox"
                                checked={published}
                                onChange={(e) => setPublished(e.target.checked)}
                                className="peer sr-only"
                            />
                            <span className="h-6 w-11 rounded-full bg-gray-300 transition-colors peer-checked:bg-emerald-500" />
                            <span className="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5" />
                        </span>
                    </label>

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
