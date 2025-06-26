import nltk
from nltk.corpus import stopwords
from nltk.tokenize import word_tokenize, sent_tokenize

def summarize_text(input_text):
    stop_words = set(stopwords.words("english"))
    words = word_tokenize(input_text)

    freq_table = {}
    for word in words:
        word = word.lower()
        if word not in stop_words and word.isalnum():
            freq_table[word] = freq_table.get(word, 0) + 1

    sentences = sent_tokenize(input_text)
    sentence_scores = {}
    for sentence in sentences:
        sentence_wordcount = len(word_tokenize(sentence))
        for word in freq_table:
            if word in sentence.lower():
                sentence_scores[sentence] = sentence_scores.get(sentence, 0) + freq_table[word]

    # Debugging output
    print("Frequency Table:", freq_table)
    print("Sentence Scores:", sentence_scores)

    average_score = sum(sentence_scores.values()) / len(sentence_scores)

    # Lowered threshold from 1.2 to 1.0 to include more sentences
    summary = ' '.join([sentence for sentence in sentences if sentence_scores.get(sentence, 0) > 1.0 * average_score])

    # Fallback: return the first two sentences if no summary is generated
    if not summary:
        summary = ' '.join(sentences[:2])

    return summary if summary else "Summary could not be generated."
