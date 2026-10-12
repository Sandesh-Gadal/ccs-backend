import React, { useState } from "react";
import axios from "axios";

export default function CategoryForm({ category, onClose, onSaved }) {
  
    const [name, setName] = useState(category ? category.name : "");
    const [position, setPosition] = useState(category?.position ?? "");

    const submit = async (e) => {
        e.preventDefault();

        const data = { name, ...(position !== "" ? { position: Number(position) } : {}) };

        try {
            if (category) {
                // update

                await axios.put(`/categories/${category.id}`, data);
            } else {
                // create
                await axios.post("/categories", data);
            }

            onSaved();
            onClose();
        } catch (error) {
            console.error("Error saving category:", error);
            // Optional: Add user-facing error handling here
        }
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
            <div className="bg-white p-6 rounded shadow w-96">
                <h3 className="text-xl font-semibold mb-4">
                    {category ? "Edit Category" : "Add Category"}
                </h3>

                <form onSubmit={submit} className="space-y-3">
                    <input
                        type="text"
                        className="w-full border p-2 rounded"
                        placeholder="Category Name"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        required
                    />
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">
                            Display order <span className="text-gray-400">(lower shows first on the website)</span>
                        </label>
                        <input
                            type="number"
                            min="0"
                            className="w-full border p-2 rounded"
                            placeholder="Leave empty to add at the end"
                            value={position}
                            onChange={(e) => setPosition(e.target.value)}
                        />
                    </div>

                    <div className="flex justify-end gap-2 pt-4">
                        <button
                            type="button"
                            className="px-4 py-2 bg-gray-400 text-white rounded"
                            onClick={onClose}
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            className="px-4 py-2 bg-blue-600 text-white rounded"
                        >
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
