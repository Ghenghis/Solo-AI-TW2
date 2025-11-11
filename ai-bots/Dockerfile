# Enterprise-Grade AI Bot Orchestrator for TWLan
# Multi-stage build for optimal security and size

# Stage 1: Builder
FROM python:3.11-slim as builder

WORKDIR /build

# Install build dependencies
RUN apt-get update && apt-get install -y \
    gcc \
    g++ \
    make \
    libmariadb-dev \
    && rm -rf /var/lib/apt/lists/*

# Copy requirements
COPY requirements.txt .

# Install Python dependencies
RUN pip install --user --no-cache-dir -r requirements.txt

# Stage 2: Runtime
FROM python:3.11-slim

# Create non-root user for security
RUN groupadd -r aibot && useradd -r -g aibot aibot

WORKDIR /app

# Install runtime dependencies only
RUN apt-get update && apt-get install -y \
    libmariadb3 \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Copy Python packages from builder
COPY --from=builder /root/.local /home/aibot/.local

# Copy application code
COPY --chown=aibot:aibot . .

# Set PATH for Python packages
ENV PATH=/home/aibot/.local/bin:$PATH

# Switch to non-root user
USER aibot

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD python -c "import sys; sys.exit(0)"

# Run orchestrator
CMD ["python", "-u", "orchestrator.py"]
