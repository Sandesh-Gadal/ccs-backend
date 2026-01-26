import React, { useEffect, useState } from "react";
import axios from "axios";
import CategoryForm from "../components/CategoryForm";
import DeleteModal from "../components/DeleteModal";

export default function Categories() {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selected, setSelected] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [showDelete, setShowDelete] = useState(false);

    const fetchCategories = async () => {
        setLoading(true);
        const res = await axios.get("/categories");
        setCategories(res.data);
        setLoading(false);
    };

    useEffect(() => {
        fetchCategories();
    }, []);

    const handleEdit = (category) => {
        setSelected(category);
        setShowForm(true);
    };

    const handleCreate = () => {
        setSelected(null);
        setShowForm(true);
    };

    const handleDelete = (category) => {
        setSelected(category);
        setShowDelete(true);
    };

    return (
        <div>
            <div className="flex justify-between mb-4">
                <h2 className="text-2xl font-semibold">Manage Categories</h2>
                <button
                    className="bg-blue-600 text-white px-4 py-2 rounded"
                    onClick={handleCreate}
                >
                    + Add Category
                </button>
            </div>

            {/* Category Table */}
            <div className="bg-white shadow rounded p-4">
                {loading ? (
                    <p>Loading...</p>
                ) : (
                    <table className="w-full border">
                        <thead>
                            <tr className="bg-gray-100">
                                <th className="p-2 border">ID</th>
                                <th className="p-2 border">Name</th>
                                <th className="p-2 border">Slug</th>
                                <th className="p-2 border">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            {categories.map((c) => (
                                <tr key={c.id}>
                                    <td className="p-2 border">{c.id}</td>
                                    <td className="p-2 border">{c.name}</td>
                                    <td className="p-2 border">{c.slug}</td>
                                    <td className="p-2 border flex gap-2">
                                        <button
                                            className="bg-yellow-500 text-white px-3 py-1 rounded"
                                            onClick={() => handleEdit(c)}
                                        >
                                            Edit
                                        </button>
                                        <button
                                            className="bg-red-600 text-white px-3 py-1 rounded"
                                            onClick={() => handleDelete(c)}
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>

            {/* Modals */}
            {showForm && (
                <CategoryForm
                    category={selected}
                    onClose={() => setShowForm(false)}
                    onSaved={fetchCategories}
                />
            )}

            {showDelete && (
                <DeleteModal
                    category={selected}
                    onClose={() => setShowDelete(false)}
                    onDelete={fetchCategories}
                />
            )}
        </div>
    );
}
