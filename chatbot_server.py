from flask import Flask, request, jsonify
import chromadb
from langchain.llms import Ollama
from langchain.vectorstores import Chroma

app = Flask(__name__)

# Initialize ChromaDB
chroma_client = chromadb.PersistentClient(path="./chromadb_data")
retriever = Chroma(chroma_client)

# Load Aprilo LLM Model (LLaMA3.2 via Ollama)
llm = Ollama(model="llama3.2")

@app.route("/chatbot/query", methods=["POST"])
def handle_query():
    data = request.json
    query = data.get("query")
    user_logged_in = data.get("logged_in", False)

    if user_logged_in and data.get("query_type") == "order_status":
        return jsonify({"response": f"Your order {data['order_id']} is in processing."})

    # Fetch response from ChromaDB
    documents = retriever.similarity_search(query, k=3)
    context = " ".join([doc.page_content for doc in documents])

    # Construct prompt dynamically
    prompt = f"Answer based on knowledge base: {context}\nUser Query: {query}"

    # Get response from Aprilo LLM model
    answer = llm.invoke(prompt)

    return jsonify({"response": answer})

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5005)
