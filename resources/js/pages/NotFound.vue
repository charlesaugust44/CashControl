<template>
    <div class="not-found">
        <div class="not-found-content">
            <div class="error-code">404</div>
            <h1>Page Not Found</h1>
            <p>Oops! The page you're looking for doesn't exist or has been moved.</p>
            <router-link to="/" class="btn btn-light">
                <i class="bi bi-house-door"></i>
                Back to Dashboard
            </router-link>
        </div>
    </div>
</template>

<style scoped>
.not-found {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    padding: 2rem;
}

.not-found-content {
    text-align: center;
    max-width: 500px;
}

.error-code {
    font-size: 8rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
    opacity: 0.15;
}

h1 {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    line-height: 1.6;
    opacity: 0.7;
}

.home-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid;
}

.home-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.home-button i {
    font-size: 1.1rem;
}
</style>
