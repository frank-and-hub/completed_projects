import OpenAI from "openai";
const secrets = require('../config/secrets');

const openai = new OpenAI({
  apiKey: secrets.openApiKey,
});

/**
 * Generates a description using OpenAI GPT
 * @param {number} length - Approximate character length of the description
 * @returns {Promise<string>} - Generated description
 */
const generateDescription = async (length = 100, topic) => {
  try {
    const prompt = `Write a concise and engaging product description in around ${length * 2} characters on topic ${topic}.`;

    const response = await openai.chat.completions.create({
      model: "gpt-4o-mini",
      messages: [
        { role: "user", content: prompt }
      ],
      max_tokens: Math.floor(length * 1.5 / 4),
    });

    return response.choices[0].message.content.trim();
  } catch (error) {
    console.error("Error generating description:", error);
    throw new Error("Failed to generate description");
  }
};

export default generateDescription;