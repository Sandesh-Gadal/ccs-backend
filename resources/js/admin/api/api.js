import axios from "axios";

export default axios.create({
    // Admin is served by Laravel itself, so call the API on the same origin
    baseURL: "/",
    withCredentials: true,
});
