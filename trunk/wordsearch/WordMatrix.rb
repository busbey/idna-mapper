#!/usr/bin/env ruby
require 'csv'
class WordMatrix
	attr :matrix
	def initialize (file)
		@matrix = IO.readlines(file)
		@matrix.each_index do |index|
			@matrix[index] = @matrix[index].strip
		end
	end

	def hash
		hash = 0
		@matrix.each_index do |index|
			hash = (hash << 1 ) + @matrix[index].hash
		end
	end

	def ==(other)
		return eql?(other)
	end

	def eql?(other)
		@matrix.each_index do |index|
			if other.matrix.length < index then
				return false
			else 
				if not @matrix[index] == other.matrix[index] then
					return false
				end
			end	
		end
		return true
	end

	def swapCol (colA, colB)
		(0...@matrix.length).each do |row|
			@matrix[row][colA] ^= @matrix[row][colB]
			@matrix[row][colB] ^= @matrix[row][colA]
			@matrix[row][colA] ^= @matrix[row][colB]
		end
	end

	def swapRow (rowA, rowB)
		temp = @matrix[rowA]
		@matrix[rowA] = @matrix[rowB]
		@matrix[rowB] = temp
	end

	def rotColD (col)
		last = @matrix[@matrix.length - 1][col]
		(@matrix.length - 1).downto(1) do |row|
			@matrix[row][col] = @matrix[row -1][col]
		end
		@matrix[0][col] = last
	end

	def rotColU (col)
		first = @matrix[0][col]
		0.upto(@matrix.length - 2) do |row|
			@matrix[row][col] = @matrix[row+1][col]
		end
		@matrix[@matrix.length - 1][col] = first
	end

	def rotRowR (row)
		last = @matrix[row][@matrix[row].length - 1]
		(@matrix[row].length-1).downto(1) do |col|
			@matrix[row][col] = @matrix[row][col-1]
		end
		@matrix[row][0] = last
	end

	def rotRowL (row)
		first = @matrix[row][0]
		0.upto(@matrix[row].length - 2) do |col|
			@matrix[row][col] = @matrix[row][col+1]
		end
		@matrix[row][@matrix[row].length - 1] = first
	end

	def addCol (colA, colB)
		(0...@matrix.length).each do |row|
			@matrix[row][colA] = (@matrix[row][colA] + @matrix[row][colB])%(?z - ?a + 1) + ?a
		end
	end

	def addRow (rowA, rowB)
		(0...@matrix[rowA].length).each do |col|
			@matrix[rowA][col] = (@matrix[rowA][col] + @matrix[rowB][col])%(?z - ?a + 1) + ?a
		end
	end

	def score (points)
		# account for looking forward and reverse
		# by doubling our word array
		bothways = {}
		points.each do |word, score|
			if word == word.reverse then
				bothways[word] = 2*score
			else
				bothways[word] = score
				bothways[word.reverse] = score
			end
		end
		# turn our strings into matrices of characters
		charMatrix = []
		charMatrix = @matrix.inject([]) do |mat, row|
			charRow = []
			row.scan(/./u) do |char|
				charRow << char[0]
			end
			mat << charRow
		end
		# transpose and turn back into strings
		# 	transpose -> handle looking for vertical
		#	back to strings -> regex lib is fast for matching
		charTrans = charMatrix.transpose
		charMatrix = nil
		trans = charTrans.inject([]) do |mat, row|
			strRow = ""
			row.each do |char|
				strRow << char
			end
			mat << strRow
		end
		# handle the actual matching with a series of regex matches
		total = bothways.inject(0) do |total, info|
			word, score = info
			pattern = Regexp.new("(?="+Regexp.escape(word)+")",nil, 'u')
			@matrix.inject(trans.inject(total) do |total, col|
				total + score * col.scan(pattern).length
			end
			) do |total, row|
				total + score * row.scan(pattern).length
			end
		end
		return total
	end

	def apply (commandFile)
		lineCount = 0
		File.foreach(commandFile) do |line|
			lineCount = lineCount + 1
			break if lineCount > 1000
			#puts "got '#{line}'"
			case line
				when /^swap column ([\d]+) ([\d]+)/ui
					swapCol($1.to_i, $2.to_i)
				when /^swap row ([\d]+) ([\d]+)/ui
					swapRow($1.to_i, $2.to_i)
				when /^rotate column down ([\d]+)/ui
					rotColD($1.to_i)
				when /^rotate column up ([\d]+)/ui
					rotColU($1.to_i)
				when /^rotate row right ([\d]+)/ui
					rotRowR($1.to_i)
				when /^rotate row left ([\d]+)/ui
					rotRowL($1.to_i)
				when /^add column ([\d]+) ([\d]+)/ui
					addCol($1.to_i, $2.to_i)
				when /^add row ([\d]+) ([\d]+)/ui
					addRow($1.to_i, $2.to_i)
				else
					# Assume it's a comment
			end
		end
	end
end
if('--test' == ARGV[0] or '-t' == ARGV[0]) then
	(1..5).each do |test|
		scores = {}
		CSV::Reader.parse(File.open("test#{test}.values", 'rb')) do |row|
			scores[row[0].data] = row[1].data.to_i
		end
		matrix = WordMatrix.new("test#{test}.matrix")
		matrix.apply("test#{test}.commands")
		expected = WordMatrix.new("test#{test}.expected")
		if not matrix == expected then
			puts "test #{test} failed (resultant matrix doesn't match)"
			puts "expected:"
			p expected
			puts "got:"
			p matrix
		else
			score = matrix.score(scores)
			expectedScore = IO.readlines("test#{test}.score")[0].to_i
			if score == expectedScore then
				puts "test #{test} passed"
			else
				puts "test #{test} failed (scoring doesn't match expectation)"
				puts "expected: #{expectedScore}"
				puts "got: #{score}"
			end
		end
	end
else
	# normal work flow, read matrix, points, commands then score.
	scores = {}
	CSV::Reader.parse(File.open(ARGV[0], 'rb')) do |row|
		scores[row[0].data] = row[1].data.to_i
	end
	matrix = WordMatrix.new(ARGV[1])
	matrix.apply(ARGV[2])
	puts matrix.score(scores)
end
