import React, { useEffect, useState } from "react";
import axios from "axios";
import { Plus, Edit2, Trash2, X, ImagePlus } from "lucide-react";

const logoUrl = (path) => (path ? `/storage/${path}` : null);

function BrandForm({ brand, onClose, onSaved }) {
    const [name, setName] = useState(brand?.name || "");
    const [file, setFile] = useState(null);
    const [preview, setPreview] = useState(logoUrl(brand?.logo));
    const [removeLogo, setRemoveLogo] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);

    const onFile = (e) => {
        const f = e.target.files[0];
        if (!f) return;
        setFile(f);
        setRemoveLogo(false);
        setPreview(URL.createObjectURL(f));
    };

    const submit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setError(null);

        const data = new FormData();
        data.append("name", name);
        if (file) data.append("logo", file);
        if (removeLogo) data.append("remove_logo", "1");

        try {
            if (brand) {
                data.append("_method", "PUT");
                await axios.post(`/brands/${brand.id}`, data);
            } else {
                await axios.post("/brands", data);
            }
            onSaved();
            onClose();
        } catch (err) {
            const errors = err.response?.data?.errors;
            setError(errors ? Object.values(errors).flat().join(" ") : err.message);
        } finally {
            setSaving(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <form onSubmit={submit} className="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div className="mb-5 flex items-center justify-between">
                    <h3 className="text-xl font-semibold">{brand ? "Edit Brand" : "Add Brand"}</h3>
                    <button type="button" onClick={onClose} className="text-gray-400 hover:text-gray-700">
                        <X size={20} />
                    </button>
                </div>

                <label className="mb-1 block text-sm font-medium text-gray-700">Name *</label>
                <input
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    required
                    className="mb-4 w-full rounded-lg border border-gray-300 p-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                    placeholder="e.g. ASUS"
                />

                <label className="mb-1 block text-sm font-medium text-gray-700">Logo (PNG, JPG or WebP)</label>
                <div className="flex items-center gap-4">
                    <div className="flex h-20 w-32 items-center justify-center rounded-lg border bg-gray-50 p-2">
                        {preview && !removeLogo ? (
                            <img src={preview} alt="" className="max-h-full max-w-full object-contain" />
                        ) : (
                            <span className="text-xs text-gray-400">No logo</span>
                        )}
                    </div>
                    <div className="flex flex-col gap-2">
                        <label className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                            <ImagePlus size={16} /> Choose logo
                            <input type="file" accept="image/png,image/jpeg,image/webp" onChange={onFile} className="sr-only" />
                        </label>
                        {preview && !removeLogo && (
                            <button
                                type="button"
                                onClick={() => {
                                    setRemoveLogo(true);
                                    setFile(null);
                                }}
                                className="text-left text-sm text-red-600 hover:underline"
                            >
                                Remove logo
                            </button>
                        )}
                    </div>
                </div>

                {error && <p className="mt-4 rounded bg-red-50 p-2 text-sm text-red-700">{error}</p>}

                <div className="mt-6 flex justify-end gap-3 border-t pt-4">
                    <button type="button" onClick={onClose} className="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" disabled={saving} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                        {saving ? "Saving…" : brand ? "Update Brand" : "Create Brand"}
                    </button>
                </div>
            </form>
        </div>
    );
}

export default function Brands() {
    const [brands, setBrands] = useState([]);
    const [loading, setLoading] = useState(true);
    const [editing, setEditing] = useState(undefined); // undefined = closed, null = new
    const [deleting, setDeleting] = useState(null);

    const fetchBrands = async () => {
        setLoading(true);
        const res = await axios.get("/brands");
        setBrands(res.data);
        setLoading(false);
    };

    useEffect(() => {
        fetchBrands();
    }, []);

    const confirmDelete = async () => {
        await axios.delete(`/brands/${deleting.id}`);
        setDeleting(null);
        fetchBrands();
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-semibold">Brands</h2>
                    <p className="text-sm text-gray-500">{brands.length} brands · shown in "Shop by brand" on the website</p>
                </div>
                <button
                    onClick={() => setEditing(null)}
                    className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700"
                >
                    <Plus size={18} /> Add Brand
                </button>
            </div>

            {loading ? (
                <p>Loading...</p>
            ) : (
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
                    {brands.map((b) => (
                        <div key={b.id} className="group rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                            <div className="flex h-16 items-center justify-center">
                                {b.logo ? (
                                    <img src={logoUrl(b.logo)} alt={b.name} className="max-h-12 max-w-[80%] object-contain" />
                                ) : (
                                    <span className="text-lg font-bold text-gray-700">{b.name}</span>
                                )}
                            </div>
                            <div className="mt-3 flex items-center justify-between border-t pt-3">
                                <div>
                                    <p className="font-medium text-gray-900">{b.name}</p>
                                    <p className="text-xs text-gray-500">{b.products_count} {b.products_count === 1 ? "product" : "products"}</p>
                                </div>
                                <div className="flex gap-1">
                                    <button onClick={() => setEditing(b)} title="Edit" className="rounded-md p-1.5 text-indigo-600 hover:bg-indigo-50">
                                        <Edit2 size={16} />
                                    </button>
                                    <button onClick={() => setDeleting(b)} title="Delete" className="rounded-md p-1.5 text-red-600 hover:bg-red-50">
                                        <Trash2 size={16} />
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {editing !== undefined && <BrandForm brand={editing} onClose={() => setEditing(undefined)} onSaved={fetchBrands} />}

            {deleting && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div className="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                        <h3 className="mb-2 text-xl font-semibold">Delete {deleting.name}?</h3>
                        <p className="mb-5 text-sm text-gray-600">
                            Its {deleting.products_count} products stay in the store, just without a brand.
                        </p>
                        <div className="flex justify-end gap-3">
                            <button onClick={() => setDeleting(null)} className="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                                Cancel
                            </button>
                            <button onClick={confirmDelete} className="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
